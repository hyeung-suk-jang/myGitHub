import { Router } from 'express';
import * as productController from '../controllers/product.controller';
import { authenticate, authorize } from '../middleware/auth';
import { upload } from '../middleware/upload';

const router = Router();

router.get('/', productController.getAllProducts);
router.get('/:id', productController.getProductById);
router.get('/slug/:slug', productController.getProductBySlug);

router.post(
  '/',
  authenticate,
  authorize('ADMIN'),
  upload.array('images', 5),
  productController.createProduct
);

router.put(
  '/:id',
  authenticate,
  authorize('ADMIN'),
  upload.array('images', 5),
  productController.updateProduct
);

router.delete('/:id', authenticate, authorize('ADMIN'), productController.deleteProduct);

export default router;
