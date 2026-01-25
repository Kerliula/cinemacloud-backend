import { PerEndpointResponseTimingPolicy } from '../../src/policies/per-endpoint-response-timing.policy.ts';

describe('PerEndpointResponseTimingPolicy', () => {
  let policy: PerEndpointResponseTimingPolicy;

  beforeEach(() => {
    jest.useFakeTimers();
    policy = new PerEndpointResponseTimingPolicy();
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  describe('constructor', () => {
    it('should use default minResponseTimeMs of 1000ms', () => {
      const defaultPolicy = new PerEndpointResponseTimingPolicy();
      expect(defaultPolicy).toBeInstanceOf(PerEndpointResponseTimingPolicy);
    });

    it('should accept custom minResponseTimeMs', () => {
      const customPolicy = new PerEndpointResponseTimingPolicy(2000);
      expect(customPolicy).toBeInstanceOf(PerEndpointResponseTimingPolicy);
    });
  });

  describe('enforce', () => {
    it('should wait for remaining time when elapsed is less than minResponseTimeMs', async () => {
      const startTime = Date.now() - 500; // 500ms elapsed
      const enforcePromise = policy.enforce(startTime, 'test-endpoint');

      // Advance timers by 500ms (remaining time)
      jest.advanceTimersByTime(500);

      await expect(enforcePromise).resolves.toBeUndefined();
    });

    it('should not wait when elapsed equals minResponseTimeMs', async () => {
      const startTime = Date.now() - 1000; // 1000ms elapsed
      const enforcePromise = policy.enforce(startTime, 'test-endpoint');

      // No timers should be pending
      expect(jest.getTimerCount()).toBe(0);

      await expect(enforcePromise).resolves.toBeUndefined();
    });

    it('should not wait when elapsed exceeds minResponseTimeMs', async () => {
      const startTime = Date.now() - 1500; // 1500ms elapsed
      const enforcePromise = policy.enforce(startTime, 'test-endpoint');

      // No timers should be pending
      expect(jest.getTimerCount()).toBe(0);

      await expect(enforcePromise).resolves.toBeUndefined();
    });

    it('should wait correct amount with custom minResponseTimeMs', async () => {
      const customPolicy = new PerEndpointResponseTimingPolicy(2000);
      const startTime = Date.now() - 500; // 500ms elapsed, need to wait 1500ms
      const enforcePromise = customPolicy.enforce(startTime, 'test-endpoint');

      // Advance timers by 1500ms
      jest.advanceTimersByTime(1500);

      await expect(enforcePromise).resolves.toBeUndefined();
    });

    it('should handle zero elapsed time', async () => {
      const startTime = Date.now(); // 0ms elapsed
      const enforcePromise = policy.enforce(startTime, 'test-endpoint');

      // Advance timers by 1000ms (full wait time)
      jest.advanceTimersByTime(1000);

      await expect(enforcePromise).resolves.toBeUndefined();
    });

    it('should handle negative elapsed time (future start time)', async () => {
      const startTime = Date.now() + 500; // -500ms elapsed (future), so remaining = 1000 - (-500) = 1500ms
      const enforcePromise = policy.enforce(startTime, 'test-endpoint');

      // Advance timers by 1500ms (full wait time)
      jest.advanceTimersByTime(1500);

      await expect(enforcePromise).resolves.toBeUndefined();
    });
  });
});
