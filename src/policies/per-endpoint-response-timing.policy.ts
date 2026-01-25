import { type ResponseTimingPolicy } from '../interfaces/response-timing.policy.ts';

class PerEndpointResponseTimingPolicy implements ResponseTimingPolicy {
  private readonly defaultMinResponseTimeMs: number;
  private readonly perEndpointOverrides: Record<string, number>;

  constructor(defaultMinResponseTimeMs = 1000, perEndpointOverrides: Record<string, number> = {}) {
    this.defaultMinResponseTimeMs = defaultMinResponseTimeMs;
    this.perEndpointOverrides = perEndpointOverrides;
  }

  async enforce(startTime: number, endpoint: string): Promise<void> {
    const minTime = this.perEndpointOverrides[endpoint] ?? this.defaultMinResponseTimeMs;

    const elapsed = Date.now() - startTime;
    const remaining = minTime - elapsed;

    if (remaining > 0) {
      await new Promise(resolve => setTimeout(resolve, remaining));
    }
  }
}

export { PerEndpointResponseTimingPolicy };
export const perEndpointResponseTimingPolicy = new PerEndpointResponseTimingPolicy();
