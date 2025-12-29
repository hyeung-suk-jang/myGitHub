import { Response } from 'express';
import { z } from 'zod';
import prisma from '../config/database';
import { AppError } from '../middleware/errorHandler';
import { AuthRequest } from '../middleware/auth';

const reviewSchema = z.object({
  productId: z.string(),
  rating: z.number().int().min(1).max(5, '평점은 1~5 사이여야 합니다.'),
  comment: z.string().optional(),
});

export const getProductReviews = async (req: AuthRequest, res: Response) => {
  try {
    const { productId } = req.params;

    const reviews = await prisma.review.findMany({
      where: { productId },
      include: {
        user: {
          select: { id: true, name: true },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    const avgRating =
      reviews.length > 0
        ? reviews.reduce((sum, review) => sum + review.rating, 0) / reviews.length
        : 0;

    res.json({
      reviews,
      avgRating: Math.round(avgRating * 10) / 10,
      totalReviews: reviews.length,
    });
  } catch (error) {
    throw error;
  }
};

export const createReview = async (req: AuthRequest, res: Response) => {
  try {
    const validatedData = reviewSchema.parse(req.body);

    const product = await prisma.product.findUnique({
      where: { id: validatedData.productId },
    });

    if (!product) {
      throw new AppError('상품을 찾을 수 없습니다.', 404);
    }

    const hasOrdered = await prisma.orderItem.findFirst({
      where: {
        productId: validatedData.productId,
        order: {
          userId: req.user!.id,
          status: 'DELIVERED',
        },
      },
    });

    if (!hasOrdered) {
      throw new AppError('구매한 상품만 리뷰를 작성할 수 있습니다.', 403);
    }

    const existingReview = await prisma.review.findUnique({
      where: {
        userId_productId: {
          userId: req.user!.id,
          productId: validatedData.productId,
        },
      },
    });

    if (existingReview) {
      throw new AppError('이미 리뷰를 작성한 상품입니다.', 400);
    }

    const review = await prisma.review.create({
      data: {
        userId: req.user!.id,
        productId: validatedData.productId,
        rating: validatedData.rating,
        comment: validatedData.comment,
      },
      include: {
        user: {
          select: { id: true, name: true },
        },
        product: {
          select: { id: true, name: true },
        },
      },
    });

    res.status(201).json({
      message: '리뷰가 작성되었습니다.',
      review,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const updateReview = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;
    const { rating, comment } = req.body;

    const review = await prisma.review.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!review) {
      throw new AppError('리뷰를 찾을 수 없습니다.', 404);
    }

    const updatedReview = await prisma.review.update({
      where: { id },
      data: {
        rating,
        comment,
      },
      include: {
        user: {
          select: { id: true, name: true },
        },
      },
    });

    res.json({
      message: '리뷰가 수정되었습니다.',
      review: updatedReview,
    });
  } catch (error) {
    throw error;
  }
};

export const deleteReview = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;

    const review = await prisma.review.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!review) {
      throw new AppError('리뷰를 찾을 수 없습니다.', 404);
    }

    await prisma.review.delete({
      where: { id },
    });

    res.json({ message: '리뷰가 삭제되었습니다.' });
  } catch (error) {
    throw error;
  }
};
