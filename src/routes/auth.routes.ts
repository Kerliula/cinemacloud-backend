import { Router } from 'express';

import { authController } from '../controllers/auth.controller.ts';
import { validate } from '../middlewares/validation.middleware.ts';
import { registerSchema } from '../schemas/auth.ts';

const router = Router();

router.post('/users', validate(registerSchema), authController.register);

export default router;
