import { Prisma } from '@prisma/client';

import { config } from '../../src/config/env.ts';
import { HTTP_STATUS } from '../../src/constants/index.ts';
import AppError from '../../src/errors/app-error.ts';
import { EntityNotFoundError } from '../../src/errors/index.ts';
import { type UserRepository } from '../../src/interfaces/index.ts';
import { UserModel } from '../../src/models/user.model.ts';
import { LocalAuthService } from '../../src/services/local-auth.service.ts';
import { bcryptUtils, jwtUtils } from '../../src/utils/index.ts';

jest.mock('../../src/models/user.model');
jest.mock('../../src/utils');
jest.mock('../../src/config/env');

describe('LocalAuthService', () => {
  let service: LocalAuthService;
  let mockAccountSecurityService: jest.Mocked<any>;
  let mockResponseTimingPolicy: jest.Mocked<any>;
  let mockUserRepository: jest.Mocked<UserRepository>;
  let mockUserModel: jest.Mocked<typeof UserModel>;
  let mockBcryptUtils: jest.Mocked<typeof bcryptUtils>;
  let mockJwtUtils: jest.Mocked<typeof jwtUtils>;
  let mockConfig: jest.Mocked<typeof config>;

  beforeEach(() => {
    mockAccountSecurityService = {
      handleFailedLogin: jest.fn(),
      handleSuccessfulLogin: jest.fn(),
      handleLoginAttempt: jest.fn(),
    };

    mockResponseTimingPolicy = {
      enforce: jest.fn(),
    };

    mockUserRepository = {
      findByEmail: jest.fn(),
      findById: jest.fn(),
      findByEmailOrFail: jest.fn(),
      findByIdOrFail: jest.fn(),
    };

    mockUserModel = UserModel as jest.Mocked<typeof UserModel>;
    mockBcryptUtils = bcryptUtils as jest.Mocked<typeof bcryptUtils>;
    mockJwtUtils = jwtUtils as jest.Mocked<typeof jwtUtils>;
    mockConfig = config as jest.Mocked<typeof config>;
    (mockConfig as any).jwtSecret = 'testSecret';
    (mockConfig as any).jwtExpiresIn = '1h';

    service = new LocalAuthService(
      mockAccountSecurityService,
      mockResponseTimingPolicy,
      mockUserRepository
    );
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('register', () => {
    const registerData = {
      email: 'test@example.com',
      password: 'Password123',
      username: 'testuser',
    };

    it('should register user successfully', async () => {
      const hashedPassword = 'hashedPassword';
      const newUser = {
        id: 1,
        email: registerData.email,
        username: registerData.username,
        password: hashedPassword,
        failedLoginAttempts: 0,
        accountLockedUntil: null,
        lastLoginAt: null,
        createdAt: new Date(),
        updatedAt: new Date(),
        roles: [{ id: 1, name: 'User', createdAt: new Date() }],
      };
      const accessToken = 'jwtToken';

      mockBcryptUtils.hash.mockResolvedValue(hashedPassword);
      mockUserModel.create.mockResolvedValue(newUser);
      mockJwtUtils.generateToken.mockReturnValue(accessToken);

      const result = await service.register(registerData);

      expect(mockBcryptUtils.hash).toHaveBeenCalledWith(registerData.password);
      expect(mockUserModel.create).toHaveBeenCalledWith({
        email: registerData.email,
        password: hashedPassword,
        username: registerData.username,
      });
      expect(mockJwtUtils.generateToken).toHaveBeenCalledWith(
        {
          id: newUser.id,
        },
        mockConfig.jwtSecret,
        mockConfig.jwtExpiresIn
      );
      expect(result).toEqual({
        user: {
          id: newUser.id,
          email: newUser.email,
          username: newUser.username,
          roles: newUser.roles,
        },
        accessToken,
      });
    });

    it('should throw error on unique constraint violation', async () => {
      const prismaError = new Prisma.PrismaClientKnownRequestError(
        'Unique constraint failed',
        {
          code: 'P2002',
          clientVersion: '1',
        }
      );

      mockBcryptUtils.hash.mockResolvedValue('hashed');
      mockUserModel.create.mockRejectedValue(prismaError);

      await expect(service.register(registerData)).rejects.toThrow(AppError);
      await expect(service.register(registerData)).rejects.toMatchObject({
        statusCode: HTTP_STATUS.CONFLICT,
      });
    });

    it('should rethrow AppError', async () => {
      const appError = new AppError(
        HTTP_STATUS.BAD_REQUEST,
        'Validation error'
      );

      mockBcryptUtils.hash.mockResolvedValue('hashed');
      mockUserModel.create.mockRejectedValue(appError);

      await expect(service.register(registerData)).rejects.toThrow(appError);
    });

    it('should throw internal server error for other errors', async () => {
      const unknownError = new Error('Unknown error');
      mockBcryptUtils.hash.mockResolvedValue('hashed');
      mockUserModel.create.mockRejectedValue(unknownError);

      await expect(service.register(registerData)).rejects.toThrow(AppError);
      await expect(service.register(registerData)).rejects.toMatchObject({
        statusCode: HTTP_STATUS.INTERNAL_SERVER_ERROR,
      });
    });
  });

  describe('login', () => {
    const loginData = {
      email: 'test@example.com',
      password: 'Password123',
    };

    const user = {
      id: 1,
      email: loginData.email,
      username: 'testuser',
      password: 'hashedPassword',
      failedLoginAttempts: 0,
      accountLockedUntil: null,
      lastLoginAt: null,
      createdAt: new Date(),
      updatedAt: new Date(),
      roles: [{ id: 1, name: 'User', createdAt: new Date() }],
    };

    beforeEach(() => {
      // mockUserModel.findByEmail = jest.fn();
    });

    it('should login successfully', async () => {
      const accessToken = 'jwtToken';

      (mockUserRepository.findByEmailOrFail as jest.Mock).mockResolvedValue(user);
      (mockUserModel.isAccountStillLocked as jest.Mock).mockResolvedValue(
        false
      );
      mockBcryptUtils.compare.mockResolvedValue(true);
      mockJwtUtils.generateToken.mockReturnValue(accessToken);

      const result = await service.login(loginData);

      expect(mockUserRepository.findByEmailOrFail).toHaveBeenCalledWith(
        loginData.email
      );
      expect(
        mockAccountSecurityService.handleLoginAttempt
      ).toHaveBeenCalledWith(user);
      expect(mockBcryptUtils.compare).toHaveBeenCalledWith(
        loginData.password,
        user.password
      );
      expect(
        mockAccountSecurityService.handleSuccessfulLogin
      ).toHaveBeenCalledWith(user);
      expect(mockJwtUtils.generateToken).toHaveBeenCalledWith(
        {
          id: user.id,
        },
        mockConfig.jwtSecret,
        mockConfig.jwtExpiresIn
      );
      expect(mockResponseTimingPolicy.enforce).toHaveBeenCalledWith(
        expect.any(Number),
        'login'
      );
      expect(result).toEqual({
        user: {
          id: user.id,
          email: user.email,
          username: user.username,
          roles: user.roles,
        },
        accessToken,
      });
    });

    it('should throw error if user not found', async () => {
      (mockUserRepository.findByEmailOrFail as jest.Mock).mockRejectedValue(
        new EntityNotFoundError('User', 'email')
      );

      await expect(service.login(loginData)).rejects.toThrow(AppError);
      await expect(service.login(loginData)).rejects.toMatchObject({
        statusCode: HTTP_STATUS.UNAUTHORIZED,
      });
    });

    it('should throw error if account is locked', async () => {
      (mockUserRepository.findByEmailOrFail as jest.Mock).mockResolvedValue(user);
      mockAccountSecurityService.handleLoginAttempt.mockRejectedValue(
        new AppError(HTTP_STATUS.FORBIDDEN)
      );

      await expect(service.login(loginData)).rejects.toThrow(AppError);
      await expect(service.login(loginData)).rejects.toMatchObject({
        statusCode: HTTP_STATUS.FORBIDDEN,
      });
    });

    it('should throw error if password is invalid', async () => {
      (mockUserRepository.findByEmailOrFail as jest.Mock).mockResolvedValue(user);
      (mockUserModel.isAccountStillLocked as jest.Mock).mockResolvedValue(
        false
      );
      mockBcryptUtils.compare.mockResolvedValue(false);

      await expect(service.login(loginData)).rejects.toThrow(AppError);
      await expect(service.login(loginData)).rejects.toMatchObject({
        statusCode: HTTP_STATUS.UNAUTHORIZED,
      });
      expect(mockAccountSecurityService.handleFailedLogin).toHaveBeenCalledWith(
        user
      );
    });

    it('should resolve expired account lock on login', async () => {
      const userWithExpiredLock = {
        ...user,
        accountLockedUntil: new Date(Date.now() - 1000), // Expired 1 second ago
      };
      const accessToken = 'jwtToken';

      (mockUserRepository.findByEmailOrFail as jest.Mock).mockResolvedValue(
        userWithExpiredLock
      );
      mockBcryptUtils.compare.mockResolvedValue(true);
      mockJwtUtils.generateToken.mockReturnValue(accessToken);

      const result = await service.login(loginData);

      expect(
        mockAccountSecurityService.handleLoginAttempt
      ).toHaveBeenCalledWith(userWithExpiredLock);
      expect(
        mockAccountSecurityService.handleSuccessfulLogin
      ).toHaveBeenCalledWith(userWithExpiredLock);
      expect(result).toEqual({
        user: {
          id: userWithExpiredLock.id,
          email: userWithExpiredLock.email,
          username: userWithExpiredLock.username,
          roles: userWithExpiredLock.roles,
        },
        accessToken,
      });
    });

    it('should not resolve account lock if account is still locked', async () => {
      const userWithActiveLock = {
        ...user,
        accountLockedUntil: new Date(Date.now() + 10000), // Still locked
      };

      (mockUserRepository.findByEmailOrFail as jest.Mock).mockResolvedValue(
        userWithActiveLock
      );
      mockAccountSecurityService.handleLoginAttempt.mockRejectedValue(
        new AppError(HTTP_STATUS.FORBIDDEN)
      );

      await expect(service.login(loginData)).rejects.toThrow(AppError);
      await expect(service.login(loginData)).rejects.toMatchObject({
        statusCode: HTTP_STATUS.FORBIDDEN,
      });

      expect(
        mockAccountSecurityService.handleSuccessfulLogin
      ).not.toHaveBeenCalled();
    });

    it('should not resolve account lock if no lock date exists', async () => {
      const userWithoutLock = {
        ...user,
        accountLockedUntil: null,
      };
      const accessToken = 'jwtToken';

      (mockUserRepository.findByEmailOrFail as jest.Mock).mockResolvedValue(
        userWithoutLock
      );
      mockBcryptUtils.compare.mockResolvedValue(true);
      mockJwtUtils.generateToken.mockReturnValue(accessToken);

      const result = await service.login(loginData);

      expect(
        mockAccountSecurityService.handleLoginAttempt
      ).toHaveBeenCalledWith(userWithoutLock);
      expect(
        mockAccountSecurityService.handleSuccessfulLogin
      ).toHaveBeenCalledWith(userWithoutLock);
      expect(result).toEqual({
        user: {
          id: userWithoutLock.id,
          email: userWithoutLock.email,
          username: userWithoutLock.username,
          roles: userWithoutLock.roles,
        },
        accessToken,
      });
    });
  });
});
