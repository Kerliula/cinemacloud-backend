export interface ResponseTimingPolicy {
  enforce(startTime: number, endpoint: string): Promise<void>;
}
