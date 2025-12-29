import { Response } from 'express';
import { z } from 'zod';
import prisma from '../config/database';
import { AppError } from '../middleware/errorHandler';
import { AuthRequest } from '../middleware/auth';

const addressSchema = z.object({
  recipientName: z.string().min(2, '수령인 이름을 입력해주세요.'),
  phone: z.string().min(10, '연락처를 입력해주세요.'),
  zipCode: z.string().min(5, '우편번호를 입력해주세요.'),
  address: z.string().min(5, '주소를 입력해주세요.'),
  addressDetail: z.string().optional(),
  isDefault: z.boolean().optional(),
});

export const getAddresses = async (req: AuthRequest, res: Response) => {
  try {
    const addresses = await prisma.address.findMany({
      where: { userId: req.user!.id },
      orderBy: { isDefault: 'desc' },
    });

    res.json({ addresses });
  } catch (error) {
    throw error;
  }
};

export const createAddress = async (req: AuthRequest, res: Response) => {
  try {
    const validatedData = addressSchema.parse(req.body);

    if (validatedData.isDefault) {
      await prisma.address.updateMany({
        where: { userId: req.user!.id },
        data: { isDefault: false },
      });
    }

    const address = await prisma.address.create({
      data: {
        ...validatedData,
        userId: req.user!.id,
      },
    });

    res.status(201).json({
      message: '배송지가 추가되었습니다.',
      address,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const updateAddress = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = addressSchema.partial().parse(req.body);

    const existingAddress = await prisma.address.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!existingAddress) {
      throw new AppError('배송지를 찾을 수 없습니다.', 404);
    }

    if (validatedData.isDefault) {
      await prisma.address.updateMany({
        where: { userId: req.user!.id },
        data: { isDefault: false },
      });
    }

    const address = await prisma.address.update({
      where: { id },
      data: validatedData,
    });

    res.json({
      message: '배송지가 수정되었습니다.',
      address,
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ error: error.errors[0].message });
    }
    throw error;
  }
};

export const deleteAddress = async (req: AuthRequest, res: Response) => {
  try {
    const { id } = req.params;

    const address = await prisma.address.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!address) {
      throw new AppError('배송지를 찾을 수 없습니다.', 404);
    }

    await prisma.address.delete({
      where: { id },
    });

    res.json({ message: '배송지가 삭제되었습니다.' });
  } catch (error) {
    throw error;
  }
};
