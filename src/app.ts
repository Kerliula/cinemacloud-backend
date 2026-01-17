import cors from 'cors';
import express, { type Application } from 'express';
import helmet from 'helmet';

import { config } from './config/env.ts';

const app: Application = express();

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.listen(config.port);

export default app;
