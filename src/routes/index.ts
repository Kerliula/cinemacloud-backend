import { Router } from 'express';

import userRoutes from './auth.routes.ts';

const router = Router();

router.use('/auth', userRoutes);

export default router;
