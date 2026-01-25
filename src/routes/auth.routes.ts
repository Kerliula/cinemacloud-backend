import { Router } from 'express';

import { authController } from '../controllers/auth.controller.ts';
import { validate } from '../middlewares/validation.middleware.ts';
import { registerSchema, loginSchema } from '../schemas/auth.ts';

const router = Router();

router.post('/users', validate(registerSchema), authController.register);
router.post('/token', validate(loginSchema), authController.login);

export default router;
