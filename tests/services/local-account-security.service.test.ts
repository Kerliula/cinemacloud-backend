import { UserModel } from '../../src/models/user.model.ts';
import { LocalAccountSecurityService } from '../../src/services/local-account-security.service.ts';
import { type UserRepository } from '../../src/interfaces/index.ts';

jest.mock('../../src/models/user.model');

describe('LocalAccountSecurityService', () => {
  let service: LocalAccountSecurityService;
  let mockUserModel: jest.Mocked<typeof UserModel>;
  let mockUserRepository: jest.Mocked<UserRepository>;

  beforeEach(() => {
    mockUserRepository = {
      findByEmail: jest.fn(),
      findById: jest.fn(),
    };
    service = new LocalAccountSecurityService(mockUserRepository);
    mockUserModel = UserModel as jest.Mocked<typeof UserModel>;
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('handleFailedLogin', () => {
    it('should increment failed attempts when below max', async () => {
      const user = {
        id: 1,
        email: 'test@example.com',
        username: 'testuser',
        password: 'hashed',
        failedLoginAttempts: 2,
        accountLockedUntil: null,
        lastLoginAt: null,
        createdAt: new Date(),
        updatedAt: new Date(),
        roles: [],
      };

      mockUserModel.incrementFailedAttempts.mockResolvedValue(undefined);
      mockUserRepository.findById.mockResolvedValue({
        ...user,
        failedLoginAttempts: 3,
      });

      await service.handleFailedLogin(user);

      expect(mockUserModel.incrementFailedAttempts).toHaveBeenCalledWith(1);
    });

    it('should lock account when max attempts reached', async () => {
      const user = {
        id: 1,
        email: 'test@example.com',
        username: 'testuser',
        password: 'hashed',
        failedLoginAttempts: 4,
        accountLockedUntil: null,
        lastLoginAt: null,
        createdAt: new Date(),
        updatedAt: new Date(),
        roles: [],
      };

      const userAfterIncrement = {
        ...user,
        failedLoginAttempts: 5,
      };

      mockUserModel.incrementFailedAttempts.mockResolvedValue(undefined);
      mockUserModel.lockAccount.mockResolvedValue(undefined);
      mockUserRepository.findById.mockResolvedValue(userAfterIncrement);

      await service.handleFailedLogin(user);

      expect(mockUserModel.incrementFailedAttempts).toHaveBeenCalledWith(1);
      expect(mockUserModel.lockAccount).toHaveBeenCalledWith(1, 15 * 60 * 1000);
    });
  });

  describe('handleSuccessfulLogin', () => {
    it('should reset failed attempts and set last login', async () => {
      const user = {
        id: 1,
        email: 'test@example.com',
        username: 'testuser',
        password: 'hashed',
        failedLoginAttempts: 3,
        accountLockedUntil: new Date(),
        lastLoginAt: null,
        createdAt: new Date(),
        updatedAt: new Date(),
        roles: [],
      };

      mockUserModel.resetFailedAttempts.mockResolvedValue(undefined);
      mockUserModel.markLogin.mockResolvedValue(undefined);

      await service.handleSuccessfulLogin(user);

      expect(mockUserModel.resetFailedAttempts).toHaveBeenCalledWith(1);
      expect(mockUserModel.markLogin).toHaveBeenCalledWith(1);
    });
  });
});
