# Excel Import Feature for Questions

## Overview
The Excel import feature allows you to bulk import questions from an Excel file into the system. This feature is available in the Questions resource page.

## How to Use

1. **Navigate to Questions**: Go to the Questions page in the admin panel
2. **Click Import Button**: Click the "Import Questions from Excel" button (green button with upload icon) in the header
3. **Select Category**: Choose the category for the questions
4. **Upload Excel File**: Select your Excel file (.xlsx or .xls format)
5. **Submit**: Click the import button to process the file

## Excel File Format

Your Excel file must follow this exact structure:

### Headers (Row 1)
The first row must contain these exact headers (case-insensitive):
- **Question** - The question text
- **A** - Option A text
- **B** - Option B text
- **C** - Option C text
- **D** - Option D text
- **Ans** - The correct answer (must be A, B, C, or D)

### Data Rows (Row 2 onwards)
Each subsequent row should contain:
- Column 1: Question text
- Column 2: Option A
- Column 3: Option B
- Column 4: Option C
- Column 5: Option D
- Column 6: Correct answer (A, B, C, or D)

### Example Excel Structure

| Question | A | B | C | D | Ans |
|----------|---|---|---|---|-----|
| What is 2+2? | 3 | 4 | 5 | 6 | B |
| What is the capital of France? | London | Paris | Berlin | Madrid | B |

## Features

- ✅ **Category Selection**: Select the category for all imported questions
- ✅ **Randomized Questions**: Questions are not tied to a specific exam (exam_id is null) - they can be randomly selected for any exam
- ✅ **Header Validation**: Validates that Excel headers match expected format
- ✅ **Data Validation**: Validates each row before importing
- ✅ **Error Handling**: Skips invalid rows and reports errors
- ✅ **Transaction Safety**: Uses database transactions to ensure data integrity
- ✅ **Progress Feedback**: Shows success/error notifications after import
- ✅ **File Cleanup**: Automatically removes uploaded file after processing

## Validation Rules

1. **Headers**: Must match exactly (case-insensitive): Question, A, B, C, D, Ans
2. **Question Text**: Cannot be empty
3. **Options**: All four options (A, B, C, D) must have values
4. **Answer**: Must be exactly A, B, C, or D (case-insensitive)

## Error Handling

- **Invalid Headers**: Import will fail with a clear error message
- **Empty Rows**: Automatically skipped
- **Invalid Data**: Rows with missing or invalid data are skipped
- **Database Errors**: Transaction rollback ensures no partial imports

## Import Results

After import, you'll see a notification showing:
- Number of questions successfully imported
- Number of rows skipped (if any)
- Any errors are logged for review

## File Requirements

- **Format**: .xlsx (Excel 2007+) or .xls (Excel 97-2003)
- **Size**: Maximum 10MB
- **Structure**: Must have headers in first row, data in subsequent rows

## Notes

- All imported questions are set as **active** by default
- Questions are linked to the selected category only (not tied to a specific exam)
- Questions can be randomly selected for any exam since they're not exam-specific
- Each question gets 4 options (A, B, C, D) with one marked as correct
- The import process is atomic - either all valid rows import or none do

## Troubleshooting

### "Invalid Excel format" Error
- Check that your headers match exactly: Question, A, B, C, D, Ans
- Ensure headers are in the first row
- Headers are case-insensitive but spelling must match

### "File not found" Error
- Try uploading the file again
- Ensure file is in .xlsx or .xls format

### Rows Being Skipped
- Check that all required fields are filled
- Verify answer column contains only A, B, C, or D
- Check for empty rows in your Excel file

### Import Takes Too Long
- Large files may take time to process
- Consider splitting very large files into smaller batches
