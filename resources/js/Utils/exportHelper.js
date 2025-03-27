import * as XLSX from 'xlsx/xlsx.mjs';

export const exportToExcel = ({
    items = [],
    columns = [],
    fileName = 'export',
    sheetName = 'Sheet1'
}) => {
    try {
        // Create headers and data
        const headers = columns.map(col => col.label);
        const dataRows = items.map(item =>
            columns.map(col => {
                let value;
                if (col.key.includes('.')) {
                    const keys = col.key.split('.');
                    value = item;
                    for (const key of keys) {
                        value = value?.[key];
                    }
                } else {
                    value = item[col.key];
                }
                return value ?? '';
            })
        );

        // Create worksheet
        const wsData = [headers, ...dataRows];
        const ws = XLSX.utils.aoa_to_sheet(wsData);

        // Auto-size columns
        const colWidths = columns.map((_, i) => ({
            wch: Math.max(
                headers[i].length,
                ...dataRows.map(row => String(row[i]).length)
            ) + 2
        }));
        ws['!cols'] = colWidths;

        // Create workbook and append worksheet
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, sheetName);

        // Generate filename with timestamp
        const timestamp = new Date().toISOString().split('T')[0];
        const fullFileName = `${fileName}_${timestamp}.xlsx`;

        // Write file
        XLSX.writeFile(wb, fullFileName);
    } catch (error) {
        console.error('Export failed:', error);
        // Fallback to CSV if Excel export fails
        exportToCSV({ items, columns, fileName });
    }
};

// CSV Export option (no dependencies)
export const exportToCSV = ({
    items = [],
    columns = [],
    fileName = 'export'
}) => {
    try {
        const headers = columns.map(col => col.label);
        const dataRows = items.map(item =>
            columns.map(col => {
                let value;
                if (col.key.includes('.')) {
                    const keys = col.key.split('.');
                    value = item;
                    for (const key of keys) {
                        value = value?.[key];
                    }
                } else {
                    value = item[col.key];
                }
                value = String(value ?? '').replace(/"/g, '""');
                return `"${value}"`;
            }).join(',')
        );

        const csvContent = [
            headers.map(h => `"${h}"`).join(','),
            ...dataRows
        ].join('\n');

        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csvContent], {
            type: 'text/csv;charset=utf-8;'
        });

        const timestamp = new Date().toISOString().split('T')[0];
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `${fileName}_${timestamp}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('CSV Export failed:', error);
        throw error;
    }
};

// Main export function
export const exportData = ({
    items = [],
    columns = [],
    fileName = 'export',
    sheetName = 'Sheet1',
    format = 'excel'
}) => {
    if (format === 'csv') {
        return exportToCSV({ items, columns, fileName });
    }
    return exportToExcel({ items, columns, fileName, sheetName });
};

