import cors from 'cors';
import express, { type Application } from 'express';
import helmet from 'helmet';

const app: Application = express();

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.listen(process.env.PORT);

export default app;
