import { Request, Response } from 'express';
import { z } from 'zod';
import prisma from '../config/database';
import { AppError } from '../middleware/errorHandler';

const createProductSchema = z.object({
  name: z.string().min(1, '상품명을 입력해주세요.'),
  description: z.string().optional(),
  price: z.number().positive('가격은 0보다 커야 합니다.'),
  salePrice: z.number().positive().optional(),
  stock: z.number().int().min(0, '재고는 0 이상이어야 합니다.'),
  categoryId: z.string(),
  isFeatured: z.boolean().optional(),
});

export const getAllProducts = async (req: Request, res: Response) => {
  try {
    const {
      page = '1',
      limit = '12',
      categoryId,
      search,
      minPrice,
      maxPrice,
      isFeatured,
    } = req.query;

    const pageNum = parseInt(page as string);
    const limitNum = parseInt(limit as string);
    const skip = (pageNum - 1) * limitNum;

    const where: any = { isActive: true };

    if (categoryId) {
      where.categoryId = categoryId;
    }

    if (search) {
      where.OR = [
        { name: { contains: search as string } },
        { description: { contains: search as string } },
      ];
    }

    if (minPrice || maxPrice) {
      where.price = {};
      if (minPrice) where.price.gte = parseFloat(minPrice as string);
      if (maxPrice) where.price.lte = parseFloat(maxPrice as string);
    }

    if (isFeatured === 'true') {
      where.isFeatured = true;
    }

    const [products, total] = await Promise.all([
      prisma.product.findMany({
        where,
        include: {
          category: {
            select: { id: true, name: true, slug: true },
          },
        },
        skip,
        take: limitNum,
        orderBy: { createdAt: 'desc' },
      }),
      prisma.product.count({ where }),
    ]);

    res.json({
      products,
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

export const getProductById = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    const product = await prisma.product.findUnique({
      where: { id },
      include: {
        category: true,
        reviews: {
          include: {
            user: {
              select: { id: true, name: true },
            },
          },
          orderBy: { createdAt: 'desc' },
        },
      },
    });

    if (!product) {
      throw new AppError('상품을 찾을 수 없습니다.', 404);
    }

    res.json({ product });
  } catch (error) {
    throw error;
  }
};

export const getProductBySlug = async (req: Request, res: Response) => {
  try {
    const { slug } = req.params;

    const product = await prisma.product.findUnique({
      where: { slug },
      include: {
        category: true,
        reviews: {
          include: {
            user: {
              select: { id: true, name: true },
            },
          },
          orderBy: { createdAt: 'desc' },
        },
      },
    });

    if (!product) {
      throw new AppError('상품을 찾을 수 없습니다.', 404);
    }

    res.json({ product });
  } catch (error) {
    throw error;
  }
};

export const createProduct = async (req: Request, res: Response) => {
  try {
    const validatedData = createProductSchema.parse(req.body);

    const slug = validatedData.name
      .toLowerCase()
      .replace(/[^a-z0-9가-힣]+/g, '-')
      .replace(/(^-|-$)/g, '');

    const images = req.files
      ? (req.files as Express.Multer.File[]).map((file) => `/uploads/${file.filename}`)
      : [];

    const product = await prisma.product.create({
      data: {
        ...validatedData,
        slug,
        images: JSON.stringify(images),
      },
      include: {
        category: true,
      },
    });

    res.status(201).json({
      message: '상품이 등록되었습니다.',
      product,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const updateProduct = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = createProductSchema.partial().parse(req.body);

    const existingProduct = await prisma.product.findUnique({
      where: { id },
    });

    if (!existingProduct) {
      throw new AppError('상품을 찾을 수 없습니다.', 404);
    }

    let updateData: any = { ...validatedData };

    if (validatedData.name) {
      updateData.slug = validatedData.name
        .toLowerCase()
        .replace(/[^a-z0-9가-힣]+/g, '-')
        .replace(/(^-|-$)/g, '');
    }

    if (req.files && (req.files as Express.Multer.File[]).length > 0) {
      const images = (req.files as Express.Multer.File[]).map(
        (file) => `/uploads/${file.filename}`
      );
      updateData.images = JSON.stringify(images);
    }

    const product = await prisma.product.update({
      where: { id },
      data: updateData,
      include: {
        category: true,
      },
    });

    res.json({
      message: '상품이 수정되었습니다.',
      product,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const deleteProduct = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    const product = await prisma.product.findUnique({
      where: { id },
    });

    if (!product) {
      throw new AppError('상품을 찾을 수 없습니다.', 404);
    }

    await prisma.product.update({
      where: { id },
      data: { isActive: false },
    });

    res.json({ message: '상품이 삭제되었습니다.' });
  } catch (error) {
    throw error;
  }
};
