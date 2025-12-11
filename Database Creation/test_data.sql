-- Test Data for Library Management System
-- This file contains sample data for testing reader functions

-- Clear existing test data (optional - comment out if you want to preserve existing data)
-- DELETE FROM Borrows;
-- DELETE FROM Copy;
-- DELETE FROM Book;
-- DELETE FROM DVD;
-- DELETE FROM Journal;
-- DELETE FROM Document;
-- DELETE FROM Author;
-- DELETE FROM Director;
-- DELETE FROM Editor;
-- DELETE FROM Member;
-- DELETE FROM User;

-- ============================================
-- Insert Users (including test members)
-- ============================================
INSERT INTO User (UserID, Address, Password, FirstName, LastName, is_admin) VALUES
(1, '517 N 94th St, Milwaukee, WI 53226', 'admin', 'Carson', 'Pagel', 1),
(2, '456 Elm St, Springfield, IL 62702', 'member123', 'Bob', 'Reader', 0),
(3, '789 Oak Ave, Springfield, IL 62703', 'member456', 'Carol', 'Patron', 0),
(4, '321 Pine Rd, Springfield, IL 62704', 'member789', 'David', 'Borrower', 0),
(5, '654 Maple Dr, Springfield, IL 62705', 'member000', 'Emma', 'Scholar', 0);

-- ============================================
-- Insert Members
-- ============================================
INSERT INTO Member (UserID, Fines) VALUES
(2, 0.00),
(3, 2.50),
(4, 0.00),
(5, 5.00);

-- ============================================
-- Insert Authors
-- ============================================
INSERT INTO Author (AuthorID, Name) VALUES
(1, 'J.K. Rowling'),
(2, 'George R.R. Martin'),
(3, 'J.R.R. Tolkien'),
(4, 'Stephen King'),
(5, 'Agatha Christie'),
(6, 'Isaac Asimov'),
(7, 'Margaret Atwood'),
(8, 'Haruki Murakami');

-- ============================================
-- Insert Directors
-- ============================================
INSERT INTO Director (DirectorID, Name) VALUES
(1, 'Christopher Columbus'),
(2, 'Steven Spielberg'),
(3, 'Peter Jackson'),
(4, 'James Cameron'),
(5, 'Denis Villeneuve'),
(6, 'Christopher Nolan');

-- ============================================
-- Insert Editors
-- ============================================
INSERT INTO Editor (EditorID, Name) VALUES
(1, 'Donald E. Knuth'),
(2, 'Brian W. Kernighan'),
(3, 'Bjarne Stroustrup'),
(4, 'Guido van Rossum'),
(5, 'Linus Torvalds'),
(6, 'Ada Lovelace');

-- ============================================
-- Insert Documents
-- ============================================
INSERT INTO Document (DocID, Title, Publisher) VALUES
(1, 'Harry Potter and the Philosopher\'s Stone', 'Bloomsbury Publishing'),
(2, 'Harry Potter and the Chamber of Secrets', 'Bloomsbury Publishing'),
(3, 'The Hobbit', 'Allen & Unwin'),
(4, 'The Lord of the Rings: The Fellowship of the Ring', 'Allen & Unwin'),
(5, 'A Game of Thrones', 'Bantam Books'),
(6, 'A Clash of Kings', 'Bantam Books'),
(7, 'The Shining', 'Doubleday'),
(8, 'It', 'Viking'),
(9, 'Murder on the Orient Express', 'Collins Crime Club'),
(10, 'Death on the Nile', 'Collins Crime Club'),
(11, 'Foundation', 'Gnome Press'),
(12, 'Foundation and Empire', 'Gnome Press'),
(13, 'The Handmaid\'s Tale', 'McClelland and Stewart'),
(14, 'Norwegian Wood', 'Kodansha'),
(15, '1Q84', 'Shinchosha'),
(16, 'Dune', 'Chilton Books'),
(17, 'The Martian', 'Crown'),
(18, 'Project Hail Mary', 'Ballantine Books'),
(19, 'The Silent Patient', 'Verity'),
(20, 'Where the Crawdads Sing', 'Penguin');

-- ============================================
-- Insert Books
-- ============================================
INSERT INTO Book (ISBN, DocID, AuthorID) VALUES
(9780747532699, 1, 1),
(9780747538493, 2, 1),
(9780261103577, 3, 3),
(9780261103368, 4, 3),
(9780553103540, 5, 2),
(9780553108033, 6, 2),
(9780385333312, 7, 4),
(9780670813650, 8, 4),
(9780062693662, 9, 5),
(9780062073556, 10, 5),
(9780553295209, 11, 6),
(9780553293357, 12, 6),
(9780385490818, 13, 7),
(9784101001019, 14, 8),
(9780857514141, 15, 8),
(9780441172719, 16, 1),
(9780553418026, 17, 1),
(9780593135204, 18, 1),
(9781501267352, 19, 1),
(9780735224629, 20, 1);

-- ============================================
-- Insert DVDs (documents 21-23)
-- ============================================
INSERT INTO Document (DocID, Title, Publisher) VALUES
(21, 'Harry Potter and the Philosopher''s Stone (Film)', 'Warner Bros.'),
(22, 'The Lord of the Rings: The Fellowship of the Ring (Film)', 'New Line Cinema'),
(23, 'Dune (2021 Film)', 'Warner Bros.');

INSERT INTO DVD (DVDID, DocID, DirectorID) VALUES
(1, 21, 1),
(2, 22, 3),
(3, 23, 5);

-- ============================================
-- Insert Journals (documents 24-26)
-- ============================================
INSERT INTO Document (DocID, Title, Publisher) VALUES
(24, 'Nature Science Review', 'Nature Publishing Group'),
(25, 'Scientific American', 'Springer Nature'),
(26, 'The Computer Journal', 'Oxford University Press');

INSERT INTO Journal (JournalID, DocID, Volume, EditorID) VALUES
(1, 24, 1, 1),
(2, 25, 2, 2),
(3, 26, 3, 3);

-- ============================================
-- Insert Copies (multiple copies per document)
-- ============================================
INSERT INTO Copy (DocID, BranchID, CopyNum, UserID, Date) VALUES
-- DocID 1: 3 copies
(1, 1, 1, 2, '2025-01-15'),
(1, 1, 2, NULL, '2025-01-15'),
(1, 1, 3, 3, '2025-01-15'),
-- DocID 2: 2 copies
(2, 1, 1, NULL, '2025-01-16'),
(2, 1, 2, 4, '2025-01-16'),
-- DocID 3: 3 copies
(3, 2, 1, NULL, '2025-01-17'),
(3, 2, 2, NULL, '2025-01-17'),
(3, 2, 3, 5, '2025-01-17'),
-- DocID 4: 2 copies
(4, 2, 1, NULL, '2025-01-18'),
(4, 2, 2, 2, '2025-01-18'),
-- DocID 5: 3 copies
(5, 1, 1, NULL, '2025-01-19'),
(5, 1, 2, 3, '2025-01-19'),
(5, 1, 3, NULL, '2025-01-19'),
-- DocID 6: 2 copies
(6, 1, 1, NULL, '2025-01-20'),
(6, 1, 2, 4, '2025-01-20'),
-- DocID 7: 2 copies
(7, 2, 1, NULL, '2025-01-21'),
(7, 2, 2, NULL, '2025-01-21'),
-- DocID 8: 3 copies
(8, 2, 1, 5, '2025-01-22'),
(8, 2, 2, NULL, '2025-01-22'),
(8, 2, 3, NULL, '2025-01-22'),
-- DocID 9: 2 copies
(9, 1, 1, NULL, '2025-01-23'),
(9, 1, 2, NULL, '2025-01-23'),
-- DocID 10: 2 copies
(10, 1, 1, 3, '2025-01-24'),
(10, 1, 2, NULL, '2025-01-24'),
-- DocID 11: 3 copies
(11, 2, 1, NULL, '2025-01-25'),
(11, 2, 2, 2, '2025-01-25'),
(11, 2, 3, NULL, '2025-01-25'),
-- DocID 12: 2 copies
(12, 2, 1, NULL, '2025-01-26'),
(12, 2, 2, NULL, '2025-01-26'),
-- DocID 13: 3 copies
(13, 1, 1, 4, '2025-01-27'),
(13, 1, 2, NULL, '2025-01-27'),
(13, 1, 3, 5, '2025-01-27'),
-- DocID 14: 2 copies
(14, 2, 1, NULL, '2025-01-28'),
(14, 2, 2, NULL, '2025-01-28'),
-- DocID 15: 2 copies
(15, 2, 1, NULL, '2025-01-29'),
(15, 2, 2, NULL, '2025-01-29'),
-- DocID 16: 3 copies
(16, 1, 1, NULL, '2025-01-30'),
(16, 1, 2, NULL, '2025-01-30'),
(16, 1, 3, NULL, '2025-01-30'),
-- DocID 17: 2 copies
(17, 1, 1, 2, '2025-01-31'),
(17, 1, 2, NULL, '2025-01-31'),
-- DocID 18: 2 copies
(18, 2, 1, NULL, '2025-02-01'),
(18, 2, 2, NULL, '2025-02-01'),
-- DocID 19: 3 copies
(19, 1, 1, NULL, '2025-02-02'),
(19, 1, 2, NULL, '2025-02-02'),
(19, 1, 3, 3, '2025-02-02'),
-- DocID 20: 2 copies
(20, 2, 1, NULL, '2025-02-03'),
(20, 2, 2, 4, '2025-02-03');


-- ============================================
-- Insert Borrows (active and overdue loans)
-- ============================================
-- Bob (UserID 2) - has checked out some books
INSERT INTO Borrows (DocID, CopyNum, UserID, BorrowDate, ReturnDate) VALUES
(1, 2, 2, '2025-11-20', '2025-12-04'),    -- Within deadline (due Dec 4)
(17, 1, 2, '2025-11-10', '2025-11-24'),   -- OVERDUE (was due Nov 24)

-- Carol (UserID 3) - has overdue fines
(19, 1, 3, '2025-10-15', '2025-10-29'),   -- OVERDUE (was due Oct 29)
(5, 2, 3, '2025-11-15', '2025-11-29'),    -- OVERDUE (was due Nov 29)

-- David (UserID 4) - has checked out books
(2, 2, 4, '2025-11-25', '2025-12-09'),    -- Within deadline
(6, 2, 4, '2025-11-22', '2025-12-06'),    -- Within deadline
(13, 1, 4, '2025-11-18', '2025-12-02'),   -- Within deadline

-- Emma (UserID 5) - has overdue books
(3, 3, 5, '2025-09-01', '2025-09-15'),    -- OVERDUE (was due Sep 15)
(8, 1, 5, '2025-11-01', '2025-11-15'),    -- OVERDUE (was due Nov 15)
(13, 3, 5, '2025-11-20', '2025-12-04');   -- Within deadline

-- ============================================
-- Library Branches (3 locations)
-- ============================================
INSERT INTO Lib_Branch (BranchID, Address, PhoneNumber) VALUES
(1, '100 Library Lane, Springfield, IL 62701', 2175551234),
(2, '200 Book Boulevard, Springfield, IL 62702', 2175555678),
(3, '300 Reading Road, Springfield, IL 62703', 2175559999);

-- ============================================
-- Insert Librarians
-- ============================================
INSERT INTO Librarian (UserID, BranchID, StartDate) VALUES
(1, 1, '2020-01-15'),
(2, 2, '2019-06-01'),
(3, 3, '2021-03-20');

-- ============================================
-- Sample Data Summary
-- ============================================
-- Users & Roles:
--   - UserID 1: Admin (Carson Pagel) - also Librarian at Branch 1
--   - UserID 2: Member (Bob Reader) - also Librarian at Branch 2 - has 1 checkout + 1 overdue
--   - UserID 3: Member (Carol Patron) - also Librarian at Branch 3 - has $2.50 in fines, 2 overdue
--   - UserID 4: Member (David Borrower) - has 3 checkouts (all current)
--   - UserID 5: Member (Emma Scholar) - has $5.00 in fines, 2 overdue + 1 current
--
-- Library Branches: 3 locations (Springfield, IL)
--
-- Documents: 26 total
--   - 20 Books (various genres and publishers)
--   - 3 DVDs (films with directors: Christopher Columbus, Peter Jackson, Denis Villeneuve)
--   - 3 Journals (academic/science journals with editors)
--
-- Copies: 50+ total copies across documents
-- Borrows: 9 active/overdue loans (good for testing checkout, return, and fine calculation)
-- Librarians: 3 staff members assigned to different branches
