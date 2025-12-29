import { Response } from 'express';
import prisma from '../config/database';
import { AuthRequest } from '../middleware/auth';

export const getDashboardStats = async (req: AuthRequest, res: Response) => {
  try {
    const [
      totalUsers,
      totalProducts,
      totalOrders,
      totalRevenue,
      recentOrders,
      lowStockProducts,
    ] = await Promise.all([
      prisma.user.count(),
      prisma.product.count({ where: { isActive: true } }),
      prisma.order.count(),
      prisma.order.aggregate({
        _sum: { finalAmount: true },
        where: { paymentStatus: 'COMPLETED' },
      }),
      prisma.order.findMany({
        take: 10,
        orderBy: { createdAt: 'desc' },
        include: {
          user: {
            select: { id: true, name: true, email: true },
          },
          items: {
            include: {
              product: {
                select: { id: true, name: true },
              },
            },
          },
        },
      }),
      prisma.product.findMany({
        where: {
          isActive: true,
          stock: { lte: 10 },
        },
        take: 10,
        orderBy: { stock: 'asc' },
      }),
    ]);

    const ordersByStatus = await prisma.order.groupBy({
      by: ['status'],
      _count: true,
    });

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const todayOrders = await prisma.order.count({
      where: {
        createdAt: { gte: today },
      },
    });

    const todayRevenue = await prisma.order.aggregate({
      _sum: { finalAmount: true },
      where: {
        createdAt: { gte: today },
        paymentStatus: 'COMPLETED',
      },
    });

    res.json({
      stats: {
        totalUsers,
        totalProducts,
        totalOrders,
        totalRevenue: totalRevenue._sum.finalAmount || 0,
        todayOrders,
        todayRevenue: todayRevenue._sum.finalAmount || 0,
      },
      ordersByStatus,
      recentOrders,
      lowStockProducts,
    });
  } catch (error) {
    throw error;
  }
};

export const getSalesReport = async (req: AuthRequest, res: Response) => {
  try {
    const { startDate, endDate } = req.query;

    const where: any = {
      paymentStatus: 'COMPLETED',
    };

    if (startDate || endDate) {
      where.createdAt = {};
      if (startDate) where.createdAt.gte = new Date(startDate as string);
      if (endDate) where.createdAt.lte = new Date(endDate as string);
    }

    const [orders, revenue, topProducts] = await Promise.all([
      prisma.order.findMany({
        where,
        include: {
          items: {
            include: {
              product: {
                select: { id: true, name: true },
              },
            },
          },
          user: {
            select: { id: true, name: true, email: true },
          },
        },
        orderBy: { createdAt: 'desc' },
      }),
      prisma.order.aggregate({
        _sum: { finalAmount: true, totalAmount: true, shippingFee: true },
        _count: true,
        where,
      }),
      prisma.orderItem.groupBy({
        by: ['productId'],
        _sum: { quantity: true, price: true },
        _count: true,
        orderBy: {
          _sum: { quantity: 'desc' },
        },
        take: 10,
      }),
    ]);

    const topProductsWithDetails = await Promise.all(
      topProducts.map(async (item) => {
        const product = await prisma.product.findUnique({
          where: { id: item.productId },
          select: { id: true, name: true, images: true },
        });
        return {
          product,
          soldQuantity: item._sum.quantity,
          revenue: item._sum.price,
          orderCount: item._count,
        };
      })
    );

    res.json({
      orders,
      summary: {
        totalOrders: revenue._count,
        totalRevenue: revenue._sum.finalAmount || 0,
        totalProductSales: revenue._sum.totalAmount || 0,
        totalShippingFees: revenue._sum.shippingFee || 0,
      },
      topProducts: topProductsWithDetails,
    });
  } catch (error) {
    throw error;
  }
};
