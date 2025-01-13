# Redis Optimization and WordPress Object Caching

## 1. Redis Configuration
- **Eviction Policy:** Configured to `allkeys-lru` to ensure efficient eviction of least-recently-used keys.
- **Memory Configuration:**
  - `maxmemory` set to **2GB**.
  - Adjusted fragmentation thresholds:
    - `active-defrag-threshold-lower` set to **5%**.
    - `active-defrag-threshold-upper` set to **50%**.
  - Increased defragmentation cycle aggressiveness:
    - `active-defrag-cycle-min` set to **10**.
    - `active-defrag-cycle-max` set to **75**.
- **Verification:** Benchmarked Redis performance to ensure acceptable latency and throughput.

## 2. Swap File Creation
- **Created a 2GB Swap File:** Added as a safety net to handle memory spikes and prevent Redis crashes.
- **Steps:**
  - Allocated swap file using `dd` command.
  - Configured proper permissions for security.
  - Enabled the swap file and made it persistent by adding it to `/etc/fstab`.
- **Validation:** Confirmed the swap file is active and available:
  - Verified via `free -h` and `swapon --show`.

## 3. Persistent Object Cache Plugin
- Enabled and validated the Redis Object Cache plugin in WordPress.
- **Monitoring:**
  - Verified high cache hit rate of **96%**.
  - Ensured proper database interaction without performance degradation.

## Outcomes
- **Memory Fragmentation Reduction:**
  - Reduced fragmentation ratio from **6.93** to **1.52**.
- **Redis Performance Benchmark:**
  - **SET:** ~37,000 requests/second.
  - **GET:** ~36,000 requests/second.
- **Persistent Object Caching:** Operational with a high hit rate and verified functionality.

## Future Tasks/Checks
1. **Monitor Redis Usage:**
   - Periodically check memory fragmentation and defrag statistics:
     ```bash
     redis-cli info memory | grep mem_fragmentation_ratio
     redis-cli info stats | grep defrag
     ```
2. **Benchmark After Traffic Increases:**
   - Re-run Redis performance tests to validate sustained performance.
3. **Revisit Configuration:**
   - Adjust `maxmemory` and defrag settings based on traffic growth.
4. **Monitor Swap Usage:**
   - Ensure minimal usage of the swap file and adjust server memory if frequent swapping occurs.

## Repository Tag
**[optimization]**
