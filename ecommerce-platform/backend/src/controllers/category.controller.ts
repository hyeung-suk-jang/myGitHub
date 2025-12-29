import { Request, Response } from 'express';
import { z } from 'zod';
import prisma from '../config/database';
import { AppError } from '../middleware/errorHandler';

const createCategorySchema = z.object({
  name: z.string().min(1, '카테고리명을 입력해주세요.'),
  description: z.string().optional(),
  parentId: z.string().optional(),
});

export const getAllCategories = async (req: Request, res: Response) => {
  try {
    const categories = await prisma.category.findMany({
      include: {
        children: true,
        _count: {
          select: { products: true },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    res.json({ categories });
  } catch (error) {
    throw error;
  }
};

export const getCategoryById = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    const category = await prisma.category.findUnique({
      where: { id },
      include: {
        parent: true,
        children: true,
        products: {
          where: { isActive: true },
          take: 10,
        },
      },
    });

    if (!category) {
      throw new AppError('카테고리를 찾을 수 없습니다.', 404);
    }

    res.json({ category });
  } catch (error) {
    throw error;
  }
};

export const createCategory = async (req: Request, res: Response) => {
  try {
    const validatedData = createCategorySchema.parse(req.body);

    const slug = validatedData.name
      .toLowerCase()
      .replace(/[^a-z0-9가-힣]+/g, '-')
      .replace(/(^-|-$)/g, '');

    const category = await prisma.category.create({
      data: {
        ...validatedData,
        slug,
      },
      include: {
        parent: true,
      },
    });

    res.status(201).json({
      message: '카테고리가 생성되었습니다.',
      category,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const updateCategory = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = createCategorySchema.partial().parse(req.body);

    let updateData: any = { ...validatedData };

    if (validatedData.name) {
      updateData.slug = validatedData.name
        .toLowerCase()
        .replace(/[^a-z0-9가-힣]+/g, '-')
        .replace(/(^-|-$)/g, '');
    }

    const category = await prisma.category.update({
      where: { id },
      data: updateData,
      include: {
        parent: true,
      },
    });

    res.json({
      message: '카테고리가 수정되었습니다.',
      category,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const deleteCategory = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    const category = await prisma.category.findUnique({
      where: { id },
      include: {
        products: true,
        children: true,
      },
    });

    if (!category) {
      throw new AppError('카테고리를 찾을 수 없습니다.', 404);
    }

    if (category.products.length > 0) {
      throw new AppError('상품이 있는 카테고리는 삭제할 수 없습니다.', 400);
    }

    if (category.children.length > 0) {
      throw new AppError('하위 카테고리가 있는 카테고리는 삭제할 수 없습니다.', 400);
    }

    await prisma.category.delete({
      where: { id },
    });

    res.json({ message: '카테고리가 삭제되었습니다.' });
  } catch (error) {
    throw error;
  }
};
