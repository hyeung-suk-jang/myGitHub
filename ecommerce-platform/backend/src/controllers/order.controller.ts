import { Response } from 'express';
import { z } from 'zod';
import prisma from '../config/database';
import { AppError } from '../middleware/errorHandler';
import { AuthRequest } from '../middleware/auth';

const createOrderSchema = z.object({
  addressId: z.string(),
  paymentMethod: z.string(),
  items: z.array(
    z.object({
      productId: z.string(),
      quantity: z.number().int().positive(),
    })
  ),
  memo: z.string().optional(),
});

export const getOrders = async (req: AuthRequest, res: Response) => {
  try {
    const orders = await prisma.order.findMany({
      where: { userId: req.user!.id },
      include: {
        items: {
          include: {
            product: {
              select: {
                id: true,
                name: true,
                images: true,
              },
            },
          },
        },
        address: true,
      },
      orderBy: { createdAt: 'desc' },
    });

    res.json({ orders });
  } catch (error) {
    throw error;
  }
};

export const getOrderById = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;

    const order = await prisma.order.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
      include: {
        items: {
          include: {
            product: true,
          },
        },
        address: true,
      },
    });

    if (!order) {
      throw new AppError('주문을 찾을 수 없습니다.', 404);
    }

    res.json({ order });
  } catch (error) {
    throw error;
  }
};

export const createOrder = async (req: AuthRequest, res: Response) => {
  try {
    const validatedData = createOrderSchema.parse(req.body);

    const address = await prisma.address.findFirst({
      where: {
        id: validatedData.addressId,
        userId: req.user!.id,
      },
    });

    if (!address) {
      throw new AppError('배송지를 찾을 수 없습니다.', 404);
    }

    let totalAmount = 0;
    const orderItems = [];

    for (const item of validatedData.items) {
      const product = await prisma.product.findUnique({
        where: { id: item.productId },
      });

      if (!product) {
        throw new AppError(`상품을 찾을 수 없습니다: ${item.productId}`, 404);
      }

      if (product.stock < item.quantity) {
        throw new AppError(`재고가 부족합니다: ${product.name}`, 400);
      }

      const price = product.salePrice || product.price;
      totalAmount += price * item.quantity;

      orderItems.push({
        productId: item.productId,
        quantity: item.quantity,
        price,
      });
    }

    const shippingFee = totalAmount >= 50000 ? 0 : 3000;
    const finalAmount = totalAmount + shippingFee;

    const orderNumber = `ORD${Date.now()}${Math.floor(Math.random() * 1000)}`;

    const order = await prisma.order.create({
      data: {
        orderNumber,
        userId: req.user!.id,
        addressId: validatedData.addressId,
        paymentMethod: validatedData.paymentMethod,
        totalAmount,
        shippingFee,
        finalAmount,
        memo: validatedData.memo,
        items: {
          create: orderItems,
        },
      },
      include: {
        items: {
          include: {
            product: true,
          },
        },
        address: true,
      },
    });

    for (const item of validatedData.items) {
      await prisma.product.update({
        where: { id: item.productId },
        data: {
          stock: {
            decrement: item.quantity,
          },
        },
      });
    }

    await prisma.cartItem.deleteMany({
      where: {
        userId: req.user!.id,
        productId: {
          in: validatedData.items.map((item) => item.productId),
        },
      },
    });

    res.status(201).json({
      message: '주문이 완료되었습니다.',
      order,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const cancelOrder = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;

    const order = await prisma.order.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
      include: {
        items: true,
      },
    });

    if (!order) {
      throw new AppError('주문을 찾을 수 없습니다.', 404);
    }

    if (order.status !== 'PENDING' && order.status !== 'CONFIRMED') {
      throw new AppError('취소할 수 없는 주문 상태입니다.', 400);
    }

    await prisma.order.update({
      where: { id },
      data: {
        status: 'CANCELLED',
        paymentStatus: 'REFUNDED',
      },
    });

    for (const item of order.items) {
      await prisma.product.update({
        where: { id: item.productId },
        data: {
          stock: {
            increment: item.quantity,
          },
        },
      });
    }

    res.json({ message: '주문이 취소되었습니다.' });
  } catch (error) {
    throw error;
  }
};

export const getAllOrders = async (req: AuthRequest, res: Response) => {
  try {
    const { page = '1', limit = '20', status } = req.query;

    const pageNum = parseInt(page as string);
    const limitNum = parseInt(limit as string);
    const skip = (pageNum - 1) * limitNum;

    const where: any = {};
    if (status) {
      where.status = status;
    }

    const [orders, total] = await Promise.all([
      prisma.order.findMany({
        where,
        include: {
          user: {
            select: { id: true, email: true, name: true },
          },
          items: {
            include: {
              product: {
                select: { id: true, name: true },
              },
            },
          },
          address: true,
        },
        skip,
        take: limitNum,
        orderBy: { createdAt: 'desc' },
      }),
      prisma.order.count({ where }),
    ]);

    res.json({
      orders,
      pagination: {
        page: pageNum,
        limit: limitNum,
        total,
        totalPages: Math.ceil(total / limitNum),
      },
    });
  } catch (error) {
    throw error;
  }
};

export const updateOrderStatus = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;
    const { status } = req.body;

    const validStatuses = [
      'PENDING',
      'CONFIRMED',
      'PREPARING',
      'SHIPPED',
      'DELIVERED',
      'CANCELLED',
      'REFUNDED',
    ];

    if (!validStatuses.includes(status)) {
      throw new AppError('유효하지 않은 주문 상태입니다.', 400);
    }

    const order = await prisma.order.update({
      where: { id },
      data: { status },
      include: {
        items: {
          include: {
            product: true,
          },
        },
      },
    });

    res.json({
      message: '주문 상태가 업데이트되었습니다.',
      order,
    });
  } catch (error) {
    throw error;
  }
};
