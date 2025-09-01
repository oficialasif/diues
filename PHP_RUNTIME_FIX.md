# 🚨 PHP Runtime Fix for Render Deployment

## ❌ Current Error
```
bash: line 1: php: command not found
==> Exited with status 127
```

## 🔧 Solution: Fix PHP Runtime Selection

### Step 1: Update Service Configuration
1. Go to your Render service dashboard
2. Click **Settings** tab
3. Scroll down to **Build & Deploy** section

### Step 2: Fix Runtime Selection
- **Runtime**: **MUST** select `PHP` (not "Other" or "Custom")
- **Root Directory**: `backend`
- **Build Command**: Leave empty or use `echo "PHP ready"`
- **Start Command**: `php -S 0.0.0.0:$PORT`

### Step 3: Alternative Start Commands to Try
If the standard command doesn't work, try these alternatives:

```bash
# Option 1: With specific entry point
php -S 0.0.0.0:$PORT index.php

# Option 2: With document root
php -S 0.0.0.0:$PORT -t .

# Option 3: With explicit host binding
php -S 0.0.0.0:$PORT --host 0.0.0.0

# Option 4: With current directory
php -S 0.0.0.0:$PORT -t $(pwd)
```

## 🚀 Manual Service Recreation (Recommended)

If the above doesn't work, recreate the service:

### Step 1: Delete Current Service
1. Go to your service dashboard
2. Click **Settings** → **Delete Service**
3. Confirm deletion

### Step 2: Create New Service
1. Click **New +** → **Web Service**
2. Connect your GitHub repo: `oficialasif/diuesports`
3. Configure:
   - **Name**: `diu-esports-backend`
   - **Root Directory**: `backend` ⚠️ **CRITICAL**
   - **Runtime**: `PHP` ⚠️ **MUST SELECT PHP**
   - **Build Command**: Leave empty
   - **Start Command**: `php -S 0.0.0.0:$PORT`

### Step 3: Set Environment Variables
Copy all variables from `RENDER_ENVIRONMENT_VARS.md`

## 🔍 Why This Happens

The "php: command not found" error occurs when:
1. **Runtime not set to PHP** - Render doesn't install PHP
2. **Wrong root directory** - PHP files not accessible
3. **Build process failed** - PHP not properly installed

## ✅ Verification Steps

After fixing:

1. **Check Runtime**: Should show "PHP" in service settings
2. **Check Logs**: Should show PHP version info
3. **Test Health**: Visit `/test_render.php` endpoint
4. **Check API**: Visit `/api` endpoint

## 🚨 Common Mistakes to Avoid

- ❌ **Runtime**: "Other" or "Custom" instead of "PHP"
- ❌ **Root Directory**: Repository root instead of `backend`
- ❌ **Start Command**: Missing `php` command
- ❌ **Build Command**: Complex commands that fail

## 📚 Alternative Deployment Methods

If PHP runtime continues to fail:

### Option 1: Use Blueprint Deployment
1. Use your `render.yaml` file
2. Render will automatically detect PHP runtime
3. Deploy via Blueprint option

### Option 2: Docker Container
1. Create `Dockerfile` with PHP
2. Use "Docker" runtime instead
3. Build custom PHP environment

### Option 3: Static Site + API
1. Deploy PHP as static files
2. Use external PHP hosting
3. Connect via API calls

---

**🎯 The key is selecting "PHP" as Runtime when creating the service!**
