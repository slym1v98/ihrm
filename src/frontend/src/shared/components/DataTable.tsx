'use client';

import { type ReactNode } from 'react';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/shared/components/ui/table';

export interface Column<T> {
  header: string;
  accessor?: keyof T;
  cell?: (item: T, index: number) => ReactNode;
  className?: string;
  headerClassName?: string;
}

interface DataTableProps<T> {
  columns: Column<T>[];
  data: T[];
  isLoading?: boolean;
  emptyMessage?: string;
  rowKey: keyof T | ((item: T) => string);
}

export function DataTable<T>({
  columns,
  data,
  isLoading,
  emptyMessage = 'Không có dữ liệu',
  rowKey,
}: DataTableProps<T>) {
  if (isLoading) {
    return (
      <div className="rounded-lg border bg-[hsl(var(--card))]">
        <Table>
          <TableHeader>
            <TableRow>
              {columns.map((col, i) => (
                <TableHead key={i} className={col.headerClassName}>{col.header || null}</TableHead>
              ))}
            </TableRow>
          </TableHeader>
        </Table>
        <div className="flex items-center justify-center py-12">
          <p className="text-muted-foreground">Đang tải...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="rounded-lg border bg-[hsl(var(--card))]">
      <Table>
        <TableHeader>
          <TableRow>
            {columns.map((col, i) => (
              <TableHead key={i} className={col.headerClassName}>{col.header || null}</TableHead>
            ))}
          </TableRow>
        </TableHeader>
        <TableBody>
          {data.map((item, rowIndex) => {
            const key = typeof rowKey === 'function' ? rowKey(item) : String(item[rowKey]);
            return (
              <TableRow key={key}>
                {columns.map((col, colIndex) => (
                  <TableCell key={colIndex} className={col.className}>
                    {col.cell
                      ? col.cell(item, rowIndex)
                      : col.accessor
                        ? String(item[col.accessor] ?? '')
                        : null}
                  </TableCell>
                ))}
              </TableRow>
            );
          })}
          {data.length === 0 && (
            <TableRow>
              <TableCell colSpan={columns.length} className="py-8 text-center text-muted-foreground">
                {emptyMessage}
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </div>
  );
}
