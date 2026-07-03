'use client';

import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { http } from '@/core/http/client';
import { ChevronRight, ChevronDown, Building2 } from 'lucide-react';

interface OrgBranch {
  id: string; code: string; name: string;
  departments: OrgDepartment[];
}

interface OrgDepartment {
  id: string; code: string; name: string; branch_id: string; parent_id: string | null;
  children: OrgDepartment[];
}

function DeptNode({ dept, depth }: { dept: OrgDepartment; depth: number }) {
  const [expanded, setExpanded] = useState(true);
  const hasChildren = dept.children && dept.children.length > 0;

  return (
    <li>
      <div
        className="flex cursor-pointer items-center gap-1 rounded px-2 py-1 hover:bg-muted/50"
        style={{ paddingLeft: `${depth * 20 + 8}px` }}
        onClick={() => hasChildren && setExpanded(!expanded)}
      >
        {hasChildren ? (
          expanded ? <ChevronDown className="h-4 w-4 text-muted-foreground" /> : <ChevronRight className="h-4 w-4 text-muted-foreground" />
        ) : (
          <span className="w-4" />
        )}
        <span className="text-sm">{dept.name}</span>
        <span className="font-mono text-xs text-muted-foreground">{dept.code}</span>
      </div>
      {hasChildren && expanded && (
        <ul>{dept.children.map(child => <DeptNode key={child.id} dept={child} depth={depth + 1} />)}</ul>
      )}
    </li>
  );
}

export function OrgTreePage() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['org-tree'],
    queryFn: async () => {
      const res = await http.get<{ data: OrgBranch[] }>('/org-tree');
      return res.data.data;
    },
  });

  if (isLoading) return <div className="py-12 text-center text-muted-foreground">Đang tải sơ đồ tổ chức...</div>;
  if (error) return <div className="py-12 text-center text-destructive">Không thể tải sơ đồ tổ chức.</div>;

  const branches = data ?? [];

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-semibold">Sơ đồ tổ chức</h1>
        <p className="text-sm text-muted-foreground">Cấu trúc cây công ty, chi nhánh và phòng ban</p>
      </div>
      <div className="rounded-lg border bg-white p-6">
        {branches.length === 0 ? (
          <p className="py-8 text-center text-muted-foreground">Chưa có dữ liệu tổ chức</p>
        ) : (
          <ul className="space-y-4">
            {branches.map(branch => (
              <li key={branch.id}>
                <div className="flex items-center gap-2 py-2 font-medium">
                  <Building2 className="h-5 w-5 text-primary" />
                  <span>{branch.name}</span>
                  <span className="font-mono text-xs text-muted-foreground">{branch.code}</span>
                </div>
                {branch.departments.length > 0 ? (
                  <ul>
                    {branch.departments.map(dept => (
                      <DeptNode key={dept.id} dept={dept} depth={1} />
                    ))}
                  </ul>
                ) : (
                  <p className="pl-8 text-sm text-muted-foreground">Chưa có phòng ban</p>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
