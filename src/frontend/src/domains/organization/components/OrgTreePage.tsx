'use client';

import { useQuery } from '@tanstack/react-query';
import { http } from '@/core/http/client';
import { Building2 } from 'lucide-react';
import { TreeView, DefaultTreeNode, type TreeNode } from '@/shared/components/TreeView';

interface OrgDepartment extends TreeNode<OrgDepartment> {
  code: string; name: string; branch_id: string; parent_id: string | null;
}

interface OrgBranch extends TreeNode<OrgBranch> {
  code: string; name: string; departments: OrgDepartment[];
}

export function OrgTreePage() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['org-tree'],
    queryFn: async () => {
      const res = await http.get<{ data: OrgBranch[] }>('/org-tree');
      return res.data.data;
    },
  });

  if (isLoading) {
    return (
      <div className="space-y-4">
        <div className="flex items-center justify-center rounded-lg border bg-[hsl(var(--card))] py-12">
          <p className="text-muted-foreground">Đang tải sơ đồ tổ chức...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="space-y-4">
        <div className="flex items-center justify-center rounded-lg border bg-[hsl(var(--card))] py-12">
          <p className="text-destructive">Không thể tải sơ đồ tổ chức.</p>
        </div>
      </div>
    );
  }

  const branches = data ?? [];

  return (
    <div className="space-y-4">
      <div className="rounded-lg border bg-[hsl(var(--card))] p-4">
        {branches.length === 0 ? (
          <p className="py-8 text-center text-muted-foreground">Chưa có dữ liệu tổ chức</p>
        ) : (
          <ul className="space-y-3">
            {branches.map((branch) => (
              <li key={branch.id}>
                <div className="flex items-center gap-2 rounded-md px-2 py-2 font-medium">
                  <Building2 className="h-5 w-5 shrink-0 text-primary" />
                  <span className="truncate">{branch.name}</span>
                  <span className="shrink-0 font-mono text-xs text-muted-foreground">{branch.code}</span>
                </div>
                {branch.departments.length > 0 ? (
                  <TreeView<OrgDepartment>
                    data={branch.departments}
                    renderNode={(dept, depth, expanded, toggle) => (
                      <DefaultTreeNode
                        label={dept.name}
                        code={dept.code}
                        depth={depth}
                        indent={20}
                        hasChildren={dept.children ? dept.children.length > 0 : false}
                        expanded={expanded}
                        onToggle={toggle}
                      />
                    )}
                  />
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
