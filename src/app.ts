import cors from 'cors';
import express, {
  type Application,
  type Request,
  type Response,
} from 'express';
import helmet from 'helmet';

import { config } from './config/env.ts';
import HTTP_STATUS from './constants/httpStatus.ts';
import { globalErrorHandler } from './middlewares/error.middleware.ts';
import routes from './routes/index.ts';

const app: Application = express();

// Security middleware
app.use(helmet());

// CORS configuration
app.use(
  cors({
    origin: config.corsOrigin,
    credentials: true,
  })
);

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// API routes
app.use('/api', routes);

// 404 handler
app.use((_req: Request, res: Response) => {
  res.status(HTTP_STATUS.NOT_FOUND).json({
    success: false,
    message: 'Route not found',
  });
});

// Global error handling middleware
app.use(globalErrorHandler);

app.listen(config.port);

export default app;
