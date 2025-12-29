import { Response } from 'express';
import { z } from 'zod';
import prisma from '../config/database';
import { AppError } from '../middleware/errorHandler';
import { AuthRequest } from '../middleware/auth';

const addToCartSchema = z.object({
  productId: z.string(),
  quantity: z.number().int().positive('수량은 1 이상이어야 합니다.'),
});

export const getCart = async (req: AuthRequest, res: Response) => {
  try {
    const cartItems = await prisma.cartItem.findMany({
      where: { userId: req.user!.id },
      include: {
        product: {
          include: {
            category: {
              select: { id: true, name: true },
            },
          },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    const total = cartItems.reduce((sum, item) => {
      const price = item.product.salePrice || item.product.price;
      return sum + price * item.quantity;
    }, 0);

    res.json({ cartItems, total });
  } catch (error) {
    throw error;
  }
};

export const addToCart = async (req: AuthRequest, res: Response) => {
  try {
    const validatedData = addToCartSchema.parse(req.body);

    const product = await prisma.product.findUnique({
      where: { id: validatedData.productId },
    });

    if (!product) {
      throw new AppError('상품을 찾을 수 없습니다.', 404);
    }

    if (product.stock < validatedData.quantity) {
      throw new AppError('재고가 부족합니다.', 400);
    }

    const existingCartItem = await prisma.cartItem.findUnique({
      where: {
        userId_productId: {
          userId: req.user!.id,
          productId: validatedData.productId,
        },
      },
    });

    let cartItem;

    if (existingCartItem) {
      cartItem = await prisma.cartItem.update({
        where: { id: existingCartItem.id },
        data: {
          quantity: existingCartItem.quantity + validatedData.quantity,
        },
        include: {
          product: true,
        },
      });
    } else {
      cartItem = await prisma.cartItem.create({
        data: {
          userId: req.user!.id,
          productId: validatedData.productId,
          quantity: validatedData.quantity,
        },
        include: {
          product: true,
        },
      });
    }

    res.status(201).json({
      message: '장바구니에 추가되었습니다.',
      cartItem,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const updateCartItem = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;
    const { quantity } = req.body;

    if (quantity < 1) {
      throw new AppError('수량은 1 이상이어야 합니다.', 400);
    }

    const cartItem = await prisma.cartItem.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
      include: {
        product: true,
      },
    });

    if (!cartItem) {
      throw new AppError('장바구니 항목을 찾을 수 없습니다.', 404);
    }

    if (cartItem.product.stock < quantity) {
      throw new AppError('재고가 부족합니다.', 400);
    }

    const updatedCartItem = await prisma.cartItem.update({
      where: { id },
      data: { quantity },
      include: {
        product: true,
      },
    });

    res.json({
      message: '장바구니가 업데이트되었습니다.',
      cartItem: updatedCartItem,
    });
  } catch (error) {
    throw error;
  }
};

export const removeFromCart = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;

    const cartItem = await prisma.cartItem.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!cartItem) {
      throw new AppError('장바구니 항목을 찾을 수 없습니다.', 404);
    }

    await prisma.cartItem.delete({
      where: { id },
    });

    res.json({ message: '장바구니에서 삭제되었습니다.' });
  } catch (error) {
    throw error;
  }
};

export const clearCart = async (req: AuthRequest, res: Response) => {
  try {
    await prisma.cartItem.deleteMany({
      where: { userId: req.user!.id },
    });

    res.json({ message: '장바구니가 비워졌습니다.' });
  } catch (error) {
    throw error;
  }
};
