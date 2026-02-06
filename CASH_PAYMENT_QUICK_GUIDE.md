# Cash Payment Recording - Quick Start Guide

## Feature Overview
Record cash payments from students directly from the Attendance Marking page using barcode scanner. Automatically looks up student, displays pending fees, and records payment with optional receipt printing.

## Step-by-Step Usage

### 1. Open Attendance Marking Page
- Navigate to Attendance module
- Select the class/session you're marking

### 2. Click "Record Payment" Button
- Look for the purple "Record Payment" button in the toolbar
- A dialog window will open with "Record Cash Payment" title
- Barcode/ID input field will be auto-focused

### 3. Scan or Enter Student ID
- **Option A**: Scan student barcode (if barcode reader available)
- **Option B**: Type student ID manually
- Press **Enter** key to search

**What happens:**
- System searches for student by ID/username/phone
- If found: Student name and ID card appears
- If not found: Error message "Student not found" displays

### 4. Select Class/Fee to Pay For
- List of pending fees appears below student card
- Each item shows:
  - Course name
  - Month/period (e.g., "February 2026")
  - Fee amount (e.g., "LKR 5,000")
- Click on a class to select it (radio button)

**What happens:**
- Payment amount automatically populates from selected fee
- Notes field becomes available

### 5. Verify and Edit Amount (Optional)
- Amount field shows the course fee
- You can manually adjust if needed
- Note: Make sure amount is correct before submitting

### 6. Add Notes (Optional)
- Use notes field for any additional information
- Examples:
  - "Partial payment - balance due"
  - "Cash received - deposited to account"
  - "Student requested receipt"

### 7. Click "Record Payment"
- Button is enabled only when:
  - Student is selected
  - Class is selected
  - Amount is greater than 0

**What happens:**
- Payment processing indicator shows
- System creates payment record in backend
- Student fee marked as paid
- Student receives notification

### 8. Receipt Printing
- Upon success, browser print dialog opens automatically
- Receipt shows:
  - Payment receipt header
  - Student name and ID
  - Amount paid
  - Payment date and time
  - Thank you message
- Click Print or Save as PDF

**If popup blocked:**
- Warning notification appears
- Try clicking OK on popup blocker permission
- Or manually print from Print button

### 9. Dialog Closes Automatically
- On successful payment
- Form resets for next payment
- You can record another payment immediately

---

## Error Messages & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "Please enter a student ID" | Barcode field empty | Type or scan student ID |
| "Student not found" | No matching student | Check student ID format |
| "No pending fees found" | Student has no outstanding fees | Student account is current on fees |
| "Failed to record payment" | Network or server error | Check internet connection, try again |
| "Please fill all required fields" | Form incomplete | Select student and class, enter amount |

---

## Tips & Tricks

### ✓ Barcode Scanner Integration
- Works with any USB/Bluetooth barcode scanner
- Scanner must output text to input field
- Works with both numeric and alphanumeric codes

### ✓ Quick Entry
1. Place cursor in barcode field (auto-focused)
2. Scan or type ID
3. Press Enter
4. Click fee in list
5. Hit Record Payment
**Total time: ~10 seconds per payment**

### ✓ Batch Payments
- Records one fee per transaction
- If student has multiple pending fees, records one at a time
- Dialog stays open for next search after payment

### ✓ Amount Adjustments
- Pre-filled amount can be edited
- Useful for partial payments or discounts
- Always verify before submitting

### ✓ Receipt Handling
- Receipts print to thermal receipt printer or PDF
- Save PDFs for student records
- Physical copies for cash reconciliation

---

## Key Features

🔍 **Smart Search**
- Finds students by: ID, Name, Email, Phone
- Case-insensitive partial matching
- Returns closest matches

💰 **Automatic Amount**
- Pulls fee amount from course record
- No manual amount lookups needed
- Always current with system data

📋 **Comprehensive Details**
- Shows course name
- Shows payment period
- Shows exact amount due
- Shows student ID for verification

🧾 **Receipt Generation**
- Automatic printing
- Professional format
- Student and payment details
- Date and amount confirmation

✔️ **Error Prevention**
- Required fields enforced
- Amount validation
- Student existence check
- Duplicate payment prevention

---

## Frequently Asked Questions

### Q: Can I record partial payments?
**A:** Yes. The amount field is editable. Enter the amount the student is paying, even if less than fee.

### Q: What if student isn't in the system?
**A:** Error "Student not found" will display. Student must be registered first.

### Q: Can I print the receipt later?
**A:** Use browser's print history or save the payment data from payment reports.

### Q: Does the payment require approval?
**A:** No. Cash payments are automatically marked as "paid" in the system.

### Q: What currencies are supported?
**A:** Currently LKR (Sri Lankan Rupees). Other currencies available on request.

### Q: Can I undo a payment?
**A:** Contact the admin. Payment can be marked as refunded in system.

### Q: Does barcode scanner work with QR codes?
**A:** Yes. Most barcode readers support QR code scanning if configured.

### Q: How many students can have pending fees?
**A:** Unlimited. System loads fees on demand for each student.

---

## System Requirements

- ✓ Desktop/Laptop (tablet supported)
- ✓ Modern browser (Chrome, Firefox, Safari, Edge)
- ✓ Internet connection
- ✓ Active admin account
- ✓ Barcode scanner (optional - can type manually)

---

## Data Recorded

When you record a cash payment, the system saves:

1. **Payment Record**
   - Student ID
   - Amount paid
   - Payment date/time
   - Payment type (Cash)
   - Your notes

2. **Fee Status**
   - Marks StudentFee as "paid"
   - Records payment ID
   - Timestamp of payment

3. **Notification**
   - Student receives in-app notification
   - Includes payment amount and date

---

## Support

If you encounter issues:
1. Check error message displayed in dialog
2. Verify student ID is correct
3. Check internet connection
4. Try refreshing the page
5. Contact admin support with error details

