# Fixing VS Code Remote Server Compatibility on Legacy CentOS Server

## 🧠 Why This Happened

The Visual Studio Code Remote Server failed to start due to an incompatibility with the `glibc` version on our CentOS server. Specifically, the server requires newer versions of `GLIBC`, `GLIBCXX`, and `CXXABI` that are not available in CentOS 7-based environments, resulting in errors like:

```
/lib64/libc.so.6: version `GLIBC_2.28' not found
```

## 🛠️ Our Fix

Rather than updating the entire OS or building a custom `glibc` version, we opted for a **safe rollback** of the VS Code Server version that works with the available `glibc`.

### 🧩 Workflow Steps

1. **Downgraded Local VS Code** to a known working version (v1.76.2) on the client:
    - Installed to a dedicated path: `C:\Program Files\Microsoft VS Code-OLD`
    - Disabled auto-updates in `settings.json`:
      ```json
      "update.mode": "manual",
      "extensions.autoUpdate": false
      ```

2. **Adjusted `settings.json`** to prevent auto-updates and ensure compatibility with PHP, SSH, and project-specific settings.

3. **Cleaned the server**:
    ```bash
    rm -rf ~/.vscode-server/bin/*
    rm -f ~/.vscode-server/*.log
    rm -f ~/.vscode-server/*.pid
    rm -f ~/.vscode-server/*.token
    ```

4. **Manually installed the older server version (v1.76.2)**:
    ```bash
    mkdir -p ~/.vscode-server/bin/1.76.2
    cd ~/.vscode-server/bin/1.76.2
    wget https://update.code.visualstudio.com/1.76.2/linux-x64/stable -O vscode-server.tar.gz
    tar -xzf vscode-server.tar.gz
    rm vscode-server.tar.gz
    ```

5. **Restarted VS Code** and confirmed successful SSH remote connection.

## ⏱️ Time Estimate

- Troubleshooting: ~1 hour
- Fix implementation: ~20 minutes
- Total time: ~1.5 hours

## 📌 Current Status

- VS Code remote connection **works**
- Locked to a stable version compatible with server `glibc`
- Future issues can be handled by repeating this method with a compatible version
