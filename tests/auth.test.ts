import request from 'supertest';

import app from '../src/app.ts';
import prisma from '../src/config/database.ts';

describe('Auth Feature Tests', () => {
  beforeAll(async () => {
    // Set test environment variables
    process.env.NODE_ENV = 'test';
    process.env.JWT_SECRET = 'test_jwt_secret';
    process.env.JWT_EXPIRES_IN = '1h';
    process.env.BCRYPT_SALT_ROUNDS = '10';

    // Seed roles
    await prisma.role.upsert({
      where: { name: 'User' },
      update: {},
      create: { name: 'User' },
    });
    await prisma.role.upsert({
      where: { name: 'Admin' },
      update: {},
      create: { name: 'Admin' },
    });

    // Clean up test data
    await prisma.user.deleteMany();
  });

  afterAll(async () => {
    // Clean up
    await prisma.user.deleteMany();
    await prisma.$disconnect();
  });

  describe('POST /api/auth/users (Register)', () => {
    it('should register a new user successfully', async () => {
      const userData = {
        email: 'test@example.com',
        password: 'Password123',
        username: 'johndoe',
      };

      const response = await request(app)
        .post('/api/auth/users')
        .send(userData)
        .expect(201);

      expect(response.body).toHaveProperty('user');
      expect(response.body).toHaveProperty('accessToken');
      expect(response.body.user.email).toBe(userData.email);
      expect(response.body.user.username).toBe(userData.username);
      expect(response.body.user).not.toHaveProperty('password');
    });

    it('should return 400 for invalid email', async () => {
      const userData = {
        email: 'invalid-email',
        password: 'Password123',
        username: 'johndoe',
      };

      const response = await request(app)
        .post('/api/auth/users')
        .send(userData)
        .expect(400);

      expect(response.body).toHaveProperty('message');
    });

    it('should return 409 for duplicate email', async () => {
      const userData = {
        email: 'test@example.com',
        password: 'Password123',
        username: 'janedoe',
      };

      // First register
      await request(app).post('/api/auth/users').send({
        email: 'test@example.com',
        password: 'Password123',
        username: 'johndoe',
      });

      // Try to register again
      const response = await request(app)
        .post('/api/auth/users')
        .send(userData)
        .expect(409);

      expect(response.body).toHaveProperty('message');
    });
  });

  describe('POST /api/auth/token (Login)', () => {
    beforeAll(async () => {
      // Create a test user for login
      await request(app).post('/api/auth/users').send({
        email: 'login@example.com',
        password: 'Password123',
        username: 'loginuser',
      });
    });

    it('should login successfully with correct credentials', async () => {
      const loginData = {
        email: 'login@example.com',
        password: 'Password123',
      };

      const response = await request(app)
        .post('/api/auth/token')
        .send(loginData)
        .expect(200);

      expect(response.body).toHaveProperty('user');
      expect(response.body).toHaveProperty('accessToken');
      expect(response.body.user.email).toBe(loginData.email);
    });

    it('should return 401 for incorrect password', async () => {
      const loginData = {
        email: 'login@example.com',
        password: 'wrongpassword',
      };

      const response = await request(app)
        .post('/api/auth/token')
        .send(loginData)
        .expect(401);

      expect(response.body).toHaveProperty('message');
    });

    it('should return 401 for non-existent user', async () => {
      const loginData = {
        email: 'nonexistent@example.com',
        password: 'password123',
      };

      const response = await request(app)
        .post('/api/auth/token')
        .send(loginData)
        .expect(401);

      expect(response.body).toHaveProperty('message');
    });
  });
});
