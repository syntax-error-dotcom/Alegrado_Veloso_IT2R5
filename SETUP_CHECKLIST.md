# Quick Setup Checklist

## Prerequisites
- MySQL database "e_library" is running
- User has login credentials working
- PHP sessions are configured properly

## Installation Checklist

- [ ] **Step 1: Create Reservations Table**
  - Open MySQL client or phpMyAdmin
  - Navigate to the "e_library" database
  - Copy and paste the SQL from `DATABASE_SCHEMA.sql`
  - Execute the SQL to create the `reservations` table
  - Verify: Run `SHOW TABLES;` and confirm "reservations" appears

- [ ] **Step 2: Verify File Permissions**
  - Ensure `public/user/discovery.php` is readable
  - Ensure `public/user/barrowing.php` is readable
  - Ensure `public/api/reserve-book.php` is readable
  - Ensure `public/api/cancel-reservation.php` is readable

- [ ] **Step 3: Test Discovery Page**
  - Navigate to `http://localhost/public/user/discovery.php` (adjust URL as needed)
  - Verify books display in genre sections
  - Click on a book - modal should appear with full details
  - Verify the modal shows: title, author, publisher, year, description

- [ ] **Step 4: Test Book Reservation (Logged-In User)**
  - Login to your account
  - Go to Discovery page
  - Click on a book to open the modal
  - Click "Book This" button
  - Should see success message
  - Verify new reservation appears in Borrowing page

- [ ] **Step 5: Test Borrowing/Reservations Page**
  - Navigate to `http://localhost/public/user/barrowing.php` (adjust URL)
  - Should see "Reserved Books" section with your booked books
  - Verify all reservation details display correctly
  - Test the "Cancel" button on a reserved book

- [ ] **Step 6: Verify Database Records**
  - Open MySQL/phpMyAdmin
  - Execute: `SELECT * FROM reservations;`
  - Verify your reservations appear in the table
  - Check that all fields are populated correctly

## Common Issues & Solutions

### Issue: Books not showing in Discovery
**Solution:** 
- Check that books table has data
- Verify category_id column exists in books table
- Check database connection in config.php

### Issue: Modal doesn't open when clicking book
**Solution:**
- Check browser console for JavaScript errors
- Verify `get-book-details.php` API is responding
- Check that jQuery is included in header

### Issue: "Book This" button doesn't work
**Solution:**
- Verify user is logged in (check session)
- Check browser console for errors
- Verify `reserve-book.php` exists and is accessible
- Check MySQL user permissions

### Issue: Reservations table not created
**Solution:**
- Verify SQL in DATABASE_SCHEMA.sql is correct
- Check MySQL user has CREATE TABLE permission
- Ensure foreign keys reference correct tables

## Database Access

### View all reservations
```sql
SELECT r.*, b.title, b.author, u.email 
FROM reservations r
JOIN books b ON r.book_id = b.book_id
JOIN users u ON r.user_id = u.user_id
ORDER BY r.reservation_date DESC;
```

### View user's reservations
```sql
SELECT r.*, b.title, b.author 
FROM reservations r
JOIN books b ON r.book_id = b.book_id
WHERE r.user_id = [USER_ID]
ORDER BY r.reservation_date DESC;
```

### Update reservation status (Admin)
```sql
UPDATE reservations 
SET status = 'pending', expected_pickup_date = NOW() + INTERVAL 3 DAY 
WHERE reservation_id = [RESERVATION_ID];
```

## API Testing (Using cURL or Postman)

### Reserve a Book
```bash
curl -X POST http://localhost/public/api/reserve-book.php \
  -H "Content-Type: application/json" \
  -d '{"uuid": "book-uuid-here"}' \
  -b "PHPSESSID=your_session_id"
```

### Cancel a Reservation
```bash
curl -X POST http://localhost/public/api/cancel-reservation.php \
  -H "Content-Type: application/json" \
  -d '{"reservation_id": 1}' \
  -b "PHPSESSID=your_session_id"
```

## Success Indicators

✅ Discovery page shows books organized by genre
✅ Clicking a book opens a detailed modal
✅ Modal displays all book information
✅ "Book This" button successfully creates a reservation
✅ Borrowing page shows user's reservations
✅ Cancel button works on reserved books
✅ All data is properly stored in the database

## Support

If you encounter any issues:
1. Check the browser console for JavaScript errors (F12)
2. Check PHP error logs for server errors
3. Verify all files are created and in correct locations
4. Ensure database structure matches DATABASE_SCHEMA.sql
5. Verify user sessions are working properly

Good luck! 🎉
