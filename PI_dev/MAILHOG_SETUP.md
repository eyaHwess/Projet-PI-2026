# 📬 MailHog Setup - No Account Needed!

## 🎯 What is MailHog?

MailHog is a **local email testing tool** that:
- ✅ Catches all emails sent by your application
- ✅ Shows them in a web interface
- ✅ Requires NO account, NO phone number, NO configuration
- ✅ Perfect for development and testing

## 🚀 Quick Setup (5 minutes)

### Step 1: Download MailHog

**Windows:**
1. Go to: https://github.com/mailhog/MailHog/releases/latest
2. Download `MailHog_windows_amd64.exe`
3. Rename it to `MailHog.exe` (optional)
4. Save it somewhere easy to find (e.g., Desktop or Downloads)

### Step 2: Run MailHog

1. **Double-click** `MailHog.exe`
2. A black console window will open - **keep it open!**
3. You'll see something like:
   ```
   [HTTP] Binding to address: 0.0.0.0:8025
   [SMTP] Binding to address: 0.0.0.0:1025
   ```

### Step 3: Configure Your Application

Your `.env.local` should already have:
```env
MAILER_DSN=smtp://localhost:1025
```

If not, add that line!

### Step 4: Clear Cache

```bash
php bin/console cache:clear
```

### Step 5: Test It!

1. **Open MailHog Web Interface**: http://localhost:8025
2. **Create a reclamation** in your app
3. **Reply as admin**
4. **Check MailHog** - you'll see the email!

## 🎨 What You'll See

MailHog shows:
- ✉️ All emails sent by your application
- 📧 From/To addresses
- 📝 Subject lines
- 🎨 HTML preview of emails
- 📄 Plain text version
- 🔍 Email headers

## 💡 Usage Tips

### Keep MailHog Running
- MailHog must be running to catch emails
- If you close the console, emails won't be caught
- Just double-click `MailHog.exe` again to restart

### Access the Web Interface
- URL: http://localhost:8025
- Refresh the page to see new emails
- Click on any email to see full details

### Clear Old Emails
- Click "Clear" button in MailHog to delete all emails
- Or just close and restart MailHog

## 🔧 Troubleshooting

### Problem: "Connection refused" error

**Solution**: Make sure MailHog is running
- Look for the black console window
- If closed, double-click `MailHog.exe` again

### Problem: Port 1025 already in use

**Solution**: Another program is using that port
```bash
# Find what's using the port
netstat -ano | findstr :1025

# Kill the process (replace PID with actual number)
taskkill /PID <PID> /F
```

### Problem: Can't access http://localhost:8025

**Solution**: Check if MailHog is running
- The console window should be open
- Try http://127.0.0.1:8025 instead

## 🎯 Alternative: Mailtrap (If you prefer web-based)

If you don't want to run MailHog locally:

1. **Sign up at**: https://mailtrap.io (FREE, email only, no phone!)
2. **Get credentials** from your inbox
3. **Configure** `.env.local`:
   ```env
   MAILER_DSN=smtp://your_username:your_password@smtp.mailtrap.io:2525
   ```

## 📊 Comparison

| Feature | MailHog | Mailtrap | Gmail |
|---------|---------|----------|-------|
| Account needed | ❌ No | ✅ Yes (email only) | ✅ Yes (phone required) |
| Installation | Download & run | None | None |
| Internet required | ❌ No | ✅ Yes | ✅ Yes |
| Free | ✅ Yes | ✅ Yes (500 emails/month) | ✅ Yes |
| Best for | Development | Development/Testing | Production |

## ✅ Recommended Setup

**For Development (Now):**
- Use **MailHog** - easiest, no account needed

**For Production (Later):**
- Use **Gmail** with your existing account
- Or use **SendGrid** (free tier: 100 emails/day)
- Or use **Mailgun** (free tier: 5000 emails/month)

## 🎉 You're Done!

Now you can:
1. ✅ Test email notifications without any account
2. ✅ See all emails in a nice web interface
3. ✅ Develop and test your reclamation system
4. ✅ Switch to real email service later for production

**Happy testing! 🚀**

---

## 📝 Quick Reference

**MailHog Download**: https://github.com/mailhog/MailHog/releases/latest

**Web Interface**: http://localhost:8025

**SMTP Configuration**: `smtp://localhost:1025`

**To Start**: Double-click `MailHog.exe`

**To Stop**: Close the console window
