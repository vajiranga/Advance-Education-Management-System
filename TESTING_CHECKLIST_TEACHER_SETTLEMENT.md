# Teacher Settlement Implementation - Testing Checklist

## Pre-Deployment Verification ✅

### Frontend (ems-admin-portal)
- [x] SettingsPage.vue syntax is valid
- [x] Old settings options removed from System Controls tab
- [x] New input fields added:
  - [x] Teacher Fee Deduction Percentage (0-100%)
  - [x] Automatic Settlement Date (1-31)
- [x] Default values set to 10 and 10
- [x] Settings ref properly updated
- [x] Save functionality uses existing API endpoint
- [x] No breaking changes to other settings tabs

### Backend (ems-backend-api)
- [x] ProcessTeacherSettlements.php created with correct syntax
- [x] Migration file created with correct schema
- [x] Payment model updated with new fillable fields
- [x] Console.php updated with daily scheduling
- [x] Uses existing SystemSetting model (no changes needed)
- [x] Uses existing Payment model (enhanced)
- [x] SystemSettingController already supports generic key-value (no changes needed)
- [x] API routes already configured (no changes needed)

---

## Post-Deployment Testing Checklist

### Database Setup
```bash
[ ] Run: php artisan migrate
[ ] Verify new columns exist in payments table
    - teacher_settlement_processed_at
    - teacher_deduction_percentage
    - teacher_deduction_amount
    - teacher_net_amount
```

### Admin Portal Testing
```bash
[ ] Access Admin Settings page
[ ] Verify "System Controls" tab now shows "Teacher Financial Settings"
[ ] Check that old options are no longer visible:
    - Block New Teacher Registration ❌
    - Auto-Approve New Classes ❌
    - Auto-Approve Extra Classes ❌
    - Allow Parent Portal Access ❌
    - Maintenance Mode ❌
[ ] Verify new fields are visible and editable
[ ] Test entering values:
    - Percentage: 15
    - Settlement Date: 20
[ ] Click "Save System Settings"
[ ] Verify success notification appears
[ ] Refresh page
[ ] Verify values persist correctly
```

### Backend Command Testing
```bash
[ ] Run: php artisan settlements:process
[ ] Check for output messages:
    - "Processing teacher settlements for day X"
    - "Deduction percentage: Y%"
    - Settlement processing details
    - "Settlement Summary" with counts
[ ] If today is settlement day, verify payment records updated
[ ] Check logs in storage/logs/laravel.log
```

### Database Verification
```sql
[ ] Check system_settings table for new entries:
    SELECT * FROM system_settings 
    WHERE key IN ('teacherFeeDeductionPercentage', 'automationSettlementDate');

[ ] If settlement ran, check payments table:
    SELECT id, amount, teacher_deduction_percentage, 
           teacher_deduction_amount, teacher_net_amount
    FROM payments 
    WHERE teacher_settlement_processed_at IS NOT NULL;
```

### Calculation Verification
```
Sample Test Case:
- Deduction Percentage: 10%
- Payment Amount: 1000
- Expected Deduction: 100
- Expected Teacher Amount: 900

Check in database that values match expected calculation
```

### Error Handling
```bash
[ ] Test with settlement date not today (e.g., set to 25, run on 6th)
    - Should see: "Today is not settlement day"
    - Should NOT process any payments
[ ] Test with no paid payments
    - Should see: "No pending payments to settle"
[ ] Test with already settled payments
    - Should NOT re-settle (checked via teacher_settlement_processed_at)
```

### API Testing
```bash
[ ] Verify GET /v1/admin/settings returns new fields
    Response should include:
    {
      "teacherFeeDeductionPercentage": "15",
      "automationSettlementDate": "20",
      ...
    }

[ ] Verify POST /v1/admin/settings accepts new fields
    Should save without errors
```

### Browser Console
```bash
[ ] Open browser DevTools (F12)
[ ] Go to Settings page
[ ] Check Console tab - should have NO errors
[ ] Check Network tab - API calls should return 200
```

### Scheduler Testing (Optional)
```bash
[ ] Check if supervisor/queue is running Laravel scheduler
[ ] Verify cron job exists if using cron:
    * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
[ ] Set settlement date to today's date
[ ] Wait for 01:00 AM or manually run command
[ ] Verify automatic processing occurs
```

---

## Expected Behavior

### When Admin Sets:
- Teacher Fee Deduction Percentage: 10%
- Automatic Settlement Date: 10

### On the 10th of Each Month:
1. Scheduler triggers at 01:00 AM
2. Command checks it's the 10th (matches settlement date)
3. Fetches all paid payments from current month not yet settled
4. For each payment:
   - Calculates 10% deduction
   - Updates payment record with:
     - teacher_settlement_processed_at: current timestamp
     - teacher_deduction_percentage: 10
     - teacher_deduction_amount: calculated amount
     - teacher_net_amount: amount - deduction
5. Outputs summary with counts

### Other Days:
- Command runs but exits early
- No payments are modified
- Minimal log output

---

## Success Criteria

✅ All checkboxes can be completed without errors
✅ No console errors in browser
✅ No exceptions in Laravel logs
✅ Settings save and persist correctly
✅ Settlement calculations are mathematically correct
✅ Old settings no longer appear in UI
✅ New settings appear and function properly
✅ Manual command execution works
✅ Automatic daily scheduling in place

---

## Rollback Plan (if needed)

```bash
# Revert migration
php artisan migrate:rollback --step=1

# Revert code changes
git checkout ems-admin-portal/src/pages/SettingsPage.vue
git checkout ems-backend-api/app/Models/Payment.php
git checkout ems-backend-api/routes/console.php
rm ems-backend-api/app/Console/Commands/ProcessTeacherSettlements.php
```

---

## Support Information

### Log Files:
- Laravel Logs: `storage/logs/laravel.log`
- Database Logs: Check your database server logs

### Debug Queries:
```php
// Check settings
SystemSetting::pluck('value', 'key');

// Check unsettled payments
Payment::whereNull('teacher_settlement_processed_at')->get();

// Check settled payments
Payment::whereNotNull('teacher_settlement_processed_at')->get();
```

### Common Issues:

**Issue**: Settings don't save
- Check: API endpoint returns 200
- Check: SystemSetting table exists
- Check: User has admin privileges

**Issue**: Command doesn't run automatically
- Check: Supervisor/queue service is running
- Check: Cron job is configured
- Check: Laravel scheduler can execute

**Issue**: Wrong deduction amounts
- Check: Settings are saved with correct values
- Check: Payment amounts are correct in database
- Check: Calculation: amount × (percentage/100)

---

**Last Updated**: February 6, 2026
**Status**: Ready for Testing
