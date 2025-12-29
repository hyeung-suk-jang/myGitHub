import { Router } from 'express';
import * as orderController from '../controllers/order.controller';
import { authenticate, authorize } from '../middleware/auth';

const router = Router();

router.use(authenticate);

router.get('/', orderController.getOrders);
router.get('/all', authorize('ADMIN'), orderController.getAllOrders);
router.get('/:id', orderController.getOrderById);
router.post('/', orderController.createOrder);
router.post('/:id/cancel', orderController.cancelOrder);
router.put('/:id/status', authorize('ADMIN'), orderController.updateOrderStatus);

export default router;
