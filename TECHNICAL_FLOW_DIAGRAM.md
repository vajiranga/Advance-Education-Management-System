# Cash Payment Recording - Technical Flow Diagram

## User Flow

```
┌─────────────────────────────────────────────────────────────┐
│         ATTENDANCE MARKING PAGE                              │
│                                                               │
│  [Attendance List]  [Import]  [Settings]  [Record Payment]   │
│                                            ▲                 │
│                                            │                 │
│                                            ▼                 │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  CASH PAYMENT DIALOG                                  │  │
│  │  ┌─────────────────────────────────────────────────┐  │  │
│  │  │ Record Cash Payment                    [Close]  │  │  │
│  │  ├─────────────────────────────────────────────────┤  │  │
│  │  │                                                 │  │  │
│  │  │ 🔍 Scan Student Barcode/ID  [_______] [x]      │  │  │
│  │  │                                      ↓          │  │  │
│  │  │                         GET /v1/users?search=.. │  │  │
│  │  │                                      ↓          │  │  │
│  │  │ ┌─────────────────────────────────────────────┐ │  │  │
│  │  │ │ ✓ John Doe                                  │ │  │  │
│  │  │ │ ID: STU001                           [x]   │ │  │  │
│  │  │ └─────────────────────────────────────────────┘ │  │  │
│  │  │              ↓                                   │  │  │
│  │  │    GET /v1/admin/students/123/pending-fees      │  │  │
│  │  │              ↓                                   │  │  │
│  │  │ Select Class to Pay For:                        │  │  │
│  │  │ ○ Mathematics (February 2026)  LKR 5,000        │  │  │
│  │  │ ○ English (February 2026)      LKR 4,500        │  │  │
│  │  │ ○ Science (February 2026)      LKR 5,500        │  │  │
│  │  │              ↓                                   │  │  │
│  │  │ Payment Amount (LKR) [5000    ]                 │  │  │
│  │  │                      (auto-filled)              │  │  │
│  │  │                                                 │  │  │
│  │  │ Notes (Optional) [________]                     │  │  │
│  │  │                                                 │  │  │
│  │  │ [Cancel]  [Record Payment] [Processing...]      │  │  │
│  │  │              ↓                                   │  │  │
│  │  │    POST /v1/admin/payments/record-cash          │  │  │
│  │  │    {                                             │  │  │
│  │  │      student_id: 123,                           │  │  │
│  │  │      amount: 5000,                              │  │  │
│  │  │      note: "...",                               │  │  │
│  │  │      fee_ids: [456]                             │  │  │
│  │  │    }                                             │  │  │
│  │  │              ↓                                   │  │  │
│  │  │    ✓ Payment recorded successfully!             │  │  │
│  │  │              ↓                                   │  │  │
│  │  │    🖨️  Print Receipt (new window opens)         │  │  │
│  │  │              ↓                                   │  │  │
│  │  │    Dialog closes & form resets                  │  │  │
│  │  │                                                 │  │  │
│  │  └─────────────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

## Data Flow Architecture

```
FRONTEND                          BACKEND                      DATABASE
(Vue 3)                          (Laravel)                     (SQLite)

┌──────────────┐
│  User clicks │
│ Record Paymt │
└──────┬───────┘
       │
       ▼
┌──────────────────┐       GET /v1/users      ┌─────────────────────┐
│ Open Dialog      │      ?search=STU001     │ UserManagementCtl   │
│ Focus Input      │      ?role=student    ──►│ index() method      │
└──────────────────┘      ?per_page=5         │                     │
       │                                       │ WHERE role='student'│
       │ [User types/scans ID]                 │ LIKE name,email,    │
       │                                       │   username,phone    │
       ▼                                       │                     │
┌──────────────────┐       Response User    ◄─┤ RETURN paginated    │
│ searchStudent()  │      {id,name,...}       │   results (5 items) │
│ API Call         │                          └─────────────────────┘
└──────────────────┘
       │
       ▼
┌──────────────────┐     GET /v1/admin/      ┌─────────────────────┐
│fetchStudentClasses│    students/123/      │ PaymentController   │
│Set Student Card  │    pending-fees      ──►│ getStudentPending   │
└──────────────────┘                         │ Fees() method       │
       │                                       │                     │
       │ Response                              │ SELECT StudentFee   │
       │ [{                                    │ WHERE student_id=123
       │   id: 456,                            │ AND status='pending'│
       │   course_name: 'Math',                │                     │
       │   amount: 5000,                       │ RETURN array of     │
       │   month_label: 'Feb 2026'             │   StudentFee rows   │
       │ }]                                    └─────────────────────┘
       │
       ▼
┌──────────────────┐
│ Populate Classes │
│ List             │
│ [User selects]   │
└──────────────────┘
       │
       ▼
┌──────────────────┐
│selectClassForPaymt│
│Auto-fill amount  │
│Enable submit     │
└──────────────────┘
       │
       │ [User enters note (opt)]
       │
       │ [User clicks Record Payment]
       │
       ▼
┌──────────────────┐    POST /v1/admin/     ┌─────────────────────┐
│ submitCashPayment│    payments/record-cash│ PaymentController   │
│ Validate         │   {                   │ recordCashPayment() │
│ API Call         │     student_id: 123,  │                     │
│ Disable button   │     amount: 5000,      │ 1. CREATE Payment:  │
└──────────────────┘     note: "...",       │    - type='cash'    │
       │                 fee_ids: [456]     │    - status='paid'  │
       │                }                   │    - user_id=123    │
       │                                    │    - amount=5000    │
       │         Response:                  │    - created_at=now │
       │         {                          │                     │
       │           message: "Success",      │ 2. UPDATE StudentFee│
       │           payment: {...}           │    - status='paid'  │
       │         }                          │    - paid_at=now    │
       │                                    │    - payment_id=payID
       ▼                                    │                     │
┌──────────────────┐                        │ 3. CREATE Notifn    │
│ printPaymentRecpt│                        │    - type='payment' │
│ Open Print Window│                        │    - user_id=123    │
│ HTML receipt     │                        │    - message=...    │
│ Auto print       │                        │                     │
└──────────────────┘                        │ RETURN Success      │
       │                                    │   + Payment obj     │
       ▼                                    └─────────────────────┘
┌──────────────────┐
│ Success Notif    │       ┌───────────────────────────────────┐
│ Show toast       │       │ DATABASE CHANGES:                 │
│ Close Dialog     │       │                                   │
│ Reset Form       │       │ payments table:                   │
│ Ready for next   │       │ └─ INSERT new row with cash info │
│ payment          │       │                                   │
└──────────────────┘       │ student_fees table:               │
                            │ └─ UPDATE row: status='paid'     │
                            │                 paid_at=now      │
                            │                 payment_id=###   │
                            │                                   │
                            │ notifications table:              │
                            │ └─ INSERT new notification       │
                            │    for student                   │
                            └───────────────────────────────────┘
```

## Error Handling Flow

```
VALIDATION LAYER                ERROR HANDLING              USER FEEDBACK

┌─────────────────┐              
│ Empty Input?    │─────NO──────►[Continue]
└────────┬────────┘
         │ YES
         ▼
    [Show Error:
     "Please enter ID"]
    [Red banner]
    
         
┌─────────────────┐
│ Search Student  │──ERROR──►[API Error]──►[Log to console]──►[Show Error:
│   Request       │                         [Extract message]   "Student
└────────┬────────┘                         [Show in banner]    not found"]
         │ SUCCESS
         │
         ▼
┌─────────────────┐
│ No results?     │──YES─────►[Show Error:
│                 │            "Student not found"]
└────────┬────────┘
         │ NO
         ▼
[Student Card Appears]

┌─────────────────┐
│Fetch Classes    │──ERROR──►[API Error]──►[Show Error:
│   Request       │                        "Failed to load fees"]
└────────┬────────┘
         │ SUCCESS
         ▼
┌─────────────────┐
│ No pending fees?│──YES─────►[Show Error:
│                 │            "No pending fees found"]
└────────┬────────┘
         │ NO
         ▼
[Classes List Shows]

┌─────────────────┐
│ Class Selected? │──NO──────►[Disable Button]
│ Amount > 0?     │
└────────┬────────┘
         │ YES
         ▼
[Enable Submit Button]

┌─────────────────┐
│ Submit Payment  │──ERROR──►[API Error (422)]──►[Show validation message]
│   Request       │                                [Show in banner]
│   (Loading)     │
└────────┬────────┘
         │ SUCCESS
         ▼
[Success Notification]
[Print Receipt]
[Close Dialog]
[Reset Form]
```

## Component State Timeline

```
INITIAL STATE (Dialog Opened)
  showCashDialog: true
  paymentForm.barcodeSearch: ''
  paymentForm.selectedStudent: null
  paymentForm.selectedClass: null
  paymentForm.amount: 0
  paymentForm.note: ''
  paymentForm.error: ''
  enrolledClasses: []
  loadingStudentData: false
  processingPayment: false
  
  UI: [Barcode Input] only visible
  
  ▼
  
STUDENT SEARCH (User presses Enter)
  loadingStudentData: true
  paymentForm.error: ''
  
  UI: Loading spinner, input disabled
  
  ▼
  
STUDENT FOUND
  loadingStudentData: false
  paymentForm.selectedStudent: {id, name, username, ...}
  enrolledClasses: [...array from API...]
  
  UI: [Student Card] + [Classes List] visible
  
  ▼
  
CLASS SELECTED (User clicks class)
  paymentForm.selectedClass: {...class object...}
  paymentForm.amount: class.amount
  
  UI: [Amount Input] + [Notes Input] visible
  [Record Button] enabled
  
  ▼
  
SUBMIT CLICKED
  processingPayment: true
  paymentForm.error: ''
  
  UI: Loading spinner on button, button disabled
  
  ▼
  
PAYMENT RECORDED
  processingPayment: false
  showCashDialog: false (auto-closed)
  All paymentForm fields: reset
  enrolledClasses: []
  
  UI: Dialog closed, form reset, ready for next payment
  Notification toast shown
  Print window opened
```

## Database Transaction Flow

```
POST /v1/admin/payments/record-cash
│
├─ VALIDATE REQUEST
│  ├─ student_id exists: users table
│  ├─ amount numeric & > 0
│  ├─ fee_ids are valid StudentFee IDs
│  └─ Check authorization (admin only)
│
├─ CREATE PAYMENT RECORD
│  └─ INSERT INTO payments
│     ├─ user_id = student_id
│     ├─ course_id = null (for bulk)
│     ├─ amount = request.amount
│     ├─ type = 'cash'
│     ├─ status = 'paid'
│     ├─ paid_at = NOW()
│     ├─ month = current month (Y-m)
│     ├─ note = request.note + ' (Admin Record)'
│     └─ created_at = NOW()
│
├─ UPDATE FEE RECORDS
│  └─ UPDATE student_fees
│     WHERE id IN (fee_ids)
│     ├─ status = 'paid'
│     ├─ payment_id = new_payment.id
│     ├─ paid_at = NOW()
│     └─ updated_at = NOW()
│
├─ CREATE NOTIFICATION
│  └─ INSERT INTO notifications
│     ├─ user_id = student_id
│     ├─ type = 'payment_success'
│     ├─ title = 'Cash Payment Recorded'
│     ├─ message = 'Admin recorded LKR X'
│     ├─ data = JSON(payment_id)
│     └─ created_at = NOW()
│
├─ RETURN RESPONSE
│  └─ 200 OK
│     {
│       "message": "Payment recorded successfully",
│       "payment": {...payment details...}
│     }
│
└─ FRONTEND
   ├─ Show success notification
   ├─ Open print receipt window
   ├─ Close dialog
   ├─ Reset form
   └─ Ready for next payment
```

## Security & Validation Layers

```
REQUEST VALIDATION
│
├─ Authentication Check
│  └─ User must be logged in (auth:sanctum middleware)
│
├─ Authorization Check
│  └─ User must have admin role (admin gate)
│
├─ Input Validation
│  ├─ student_id: required|exists:users,id
│  ├─ amount: required|numeric|min:0|max:999999
│  ├─ fee_ids: nullable|array|exists:student_fees,id
│  ├─ note: nullable|string|max:500
│  └─ All inputs trimmed and sanitized
│
├─ Business Logic Validation
│  ├─ Student must exist and not deleted
│  ├─ Amount must be positive
│  ├─ Fee IDs must belong to student
│  └─ Fees must be pending (not already paid)
│
├─ Database Constraints
│  ├─ Foreign key validation
│  ├─ NOT NULL constraints
│  ├─ Unique constraints
│  └─ Referential integrity
│
└─ Error Response
   └─ 422 Unprocessable Entity with field errors
      or
      500 Server Error with generic message (no data leak)
```

