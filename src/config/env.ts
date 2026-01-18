import dotenv from 'dotenv';

dotenv.config();

export const config = {
  nodeEnv: process.env.NODE_ENV || ('development' as string),
  port: (() => {
    const port = parseInt(process.env.PORT || '3000', 10);
    if (isNaN(port)) throw new Error('PORT must be a valid number');
    return port;
  })(),
  databaseUrl: process.env.DATABASE_URL as string,
  corsOrigin:
    process.env.CORS_ORIGIN ??
    (process.env.NODE_ENV === 'development' ? '*' : ''),
  jwtSecret: process.env.JWT_SECRET as string,
  jwtExpiresIn: process.env.JWT_EXPIRES_IN as string,
  bcryptSaltRounds: parseInt(
    process.env.BCRYPT_SALT_ROUNDS || '10',
    10
  ) as number,
} as const;

const requiredEnvVars = ['DATABASE_URL', 'JWT_SECRET', 'JWT_EXPIRES_IN'];

for (const envVar of requiredEnvVars) {
  if (!process.env[envVar]) {
    throw new Error(`Missing required environment variable: ${envVar}`);
  }
}
