export const ERROR_MESSAGES: Record<string, string> = {
  // Branch
  BRANCH_NOT_FOUND: 'Không tìm thấy chi nhánh',
  BRANCH_HAS_ACTIVE_DEPARTMENTS: 'Không thể vô hiệu hóa chi nhánh có phòng ban đang hoạt động',
  DUPLICATE_BRANCH_CODE: 'Mã chi nhánh đã tồn tại',

  // Department
  DEPARTMENT_NOT_FOUND: 'Không tìm thấy phòng ban',
  DEPARTMENT_HAS_ACTIVE_CHILDREN: 'Không thể vô hiệu hóa phòng ban có phòng ban con đang hoạt động',
  DEPARTMENT_NOT_IN_SAME_BRANCH: 'Phòng ban cha phải thuộc cùng chi nhánh',
  CIRCULAR_MOVE: 'Không thể chuyển phòng ban vào chính nó hoặc con của nó',
  DUPLICATE_DEPARTMENT_CODE: 'Mã phòng ban đã tồn tại',

  // Position
  POSITION_NOT_FOUND: 'Không tìm thấy chức vụ',
  DUPLICATE_POSITION_CODE: 'Mã chức vụ đã tồn tại',

  // Common
  INVALID_ORGANIZATION_CODE: 'Mã không hợp lệ (phải viết hoa, bắt đầu bằng chữ)',
  UNAUTHENTICATED: 'Phiên đăng nhập hết hạn, vui lòng đăng nhập lại',
  INVALID_CREDENTIALS: 'Email hoặc mật khẩu không đúng',
  VALIDATION_ERROR: 'Dữ liệu không hợp lệ',
  FORBIDDEN: 'Bạn không có quyền thực hiện thao tác này',
};

export interface ApiErrorPayload {
  response?: {
    status?: number;
    data?: {
      error?: {
        code?: string;
        message?: string;
        details?: { field: string; message: string }[];
      };
    };
  };
}

export function extractErrorMessage(raw: unknown, fallback = 'Có lỗi xảy ra'): string {
  const err = raw as ApiErrorPayload;
  const code = err?.response?.data?.error?.code;
  if (code && ERROR_MESSAGES[code]) return ERROR_MESSAGES[code];
  return err?.response?.data?.error?.message ?? fallback;
}

export function extractFieldErrors(raw: unknown) {
  const err = raw as ApiErrorPayload;
  return err?.response?.data?.error?.details ?? [];
}
