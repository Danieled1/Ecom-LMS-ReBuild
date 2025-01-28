
# Server Optimization and Load Testing Documentation

## Phase Summary

**Date:** January 2025  
**Objective:** Optimize server performance and validate with load testing.

---

## Key Changes and Improvements

### 1. **Server Hardware Upgrades**
- **CPU:** Increased from 6 cores to 12 cores for improved concurrency and processing power.
- **Memory:** Upgraded from 16 GB to 32 GB to reduce swapping and improve cache performance.

### 2. **Web Server Optimizations**
- **Nginx Configuration:**
  - Added caching headers for static assets.
  - Enabled `gzip` compression to reduce bandwidth usage.
- **PHP-FPM Optimization:**
  - Increased `pm.max_children` to 200 to handle higher concurrent connections.
  - Adjusted `pm.max_requests` to 200 to avoid memory leaks.
  - Reduced `pm.process_idle_timeout` to 10s for efficient resource utilization.
- **Redis Configuration:**
  - Set `maxmemory` to **2GB** with `allkeys-lru` eviction policy.
  - Reduced memory fragmentation from **1.52** to **1.33**.

### 3. **Load Testing Using Siege**
- **Goal:** Validate server performance under concurrent load with **400 virtual users**.
- **Key Results:**
  - **Transaction Rate:** ~698 transactions/second.
  - **Throughput:** ~9.71 MB/second.
  - **Concurrency:** ~404 concurrent connections.
  - **Success Rate:** 98.88% (minor issues due to invalid URLs).

---

## Monitoring and Validation Commands

### Memory and Swap Usage:
```bash
free -h
swapon --show
```

### PHP-FPM Memory Usage:
```bash
ps --no-headers -o "rss,cmd" -C php-fpm | awk '{ sum+=$1; n++ } END { if (n > 0) print sum / n; }'
```

### Redis Memory and Fragmentation:
```bash
redis-cli info memory | grep 'used_memory_human\|mem_fragmentation_ratio'
```

### MySQL InnoDB Buffer Pool:
```bash
mysql -e "SHOW STATUS LIKE 'Innodb_buffer_pool_bytes_data';"
```

### Active Connections (Nginx/Apache):
```bash
netstat -anp | grep ':80\|:443' | wc -l
```

---

## Challenges Encountered

1. **Invalid URLs:**
   - URLs with `%3C%25=` caused 404 errors.
   - Solution: Cleaned the list of URLs for valid testing.

2. **Socket Failures:**
   - Siege tests initially faced socket failures due to system limits.
   - Solution: Increased file descriptor limits and optimized PHP-FPM.

3. **Redis Monitoring:**
   - Observed occasional spikes in memory fragmentation.
   - Solution: Adjusted `active-defrag` settings for better performance.

---

## Future Steps

1. **CSS/JS Minification:**
   - Minimize assets to improve page load times and reduce bandwidth usage.
   - Tools under consideration: `Terser` (for JS) and `cssnano`.

2. **Cache Validation:**
   - Ensure consistent caching behavior for logged-in and logged-out users.

3. **Monitor Long-Term Load:**
   - Continuously validate server performance with a monitoring solution (e.g., New Relic).

4. **Error Log Review:**
   - Regularly check for 500/404 errors in server logs and address them.

---

## Results and Impact

- **Load Handling:** Server now sustains up to **400 concurrent users** effectively.
- **Reduced Latency:** Response times decreased to an average of **0.58s**.
- **Higher Throughput:** Bandwidth usage improved due to better compression and caching.

---

## Repository Tag

**[optimization-phase-2]**

---

