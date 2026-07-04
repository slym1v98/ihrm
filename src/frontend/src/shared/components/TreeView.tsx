'use client';

import { useState, type ReactNode } from 'react';
import { ChevronRight, ChevronDown } from 'lucide-react';
import { cn } from '@/core/utils/cn';

export interface TreeNode<T> {
  id: string;
  children?: TreeNode<T>[];
  [key: string]: unknown;
}

interface TreeViewProps<T extends TreeNode<T>> {
  data: T[];
  renderNode: (node: T, depth: number, isExpanded: boolean, toggle: () => void) => ReactNode;
  defaultExpanded?: boolean;
  indent?: number;
}

export function TreeView<T extends TreeNode<T>>({
  data,
  renderNode,
  defaultExpanded = true,
  indent = 20,
}: TreeViewProps<T>) {
  return (
    <ul className="space-y-1">
      {data.map((node) => (
        <TreeNodeItem
          key={node.id}
          node={node}
          depth={0}
          renderNode={renderNode}
          defaultExpanded={defaultExpanded}
          indent={indent}
        />
      ))}
    </ul>
  );
}

interface TreeNodeItemProps<T extends TreeNode<T>> {
  node: T;
  depth: number;
  renderNode: (node: T, depth: number, isExpanded: boolean, toggle: () => void) => ReactNode;
  defaultExpanded: boolean;
  indent: number;
}

function TreeNodeItem<T extends TreeNode<T>>({
  node,
  depth,
  renderNode,
  defaultExpanded,
  indent,
}: TreeNodeItemProps<T>) {
  const [expanded, setExpanded] = useState(defaultExpanded);
  const hasChildren = node.children && node.children.length > 0;
  const toggle = () => hasChildren && setExpanded((v) => !v);

  return (
    <li>
      {renderNode(node, depth, expanded, toggle)}
      {hasChildren && expanded && (
        <ul>
          {node.children!.map((child) => (
            <TreeNodeItem
              key={child.id}
              node={child as T}
              depth={depth + 1}
              renderNode={renderNode}
              defaultExpanded={defaultExpanded}
              indent={indent}
            />
          ))}
        </ul>
      )}
    </li>
  );
}

export function DefaultTreeNode({
  label,
  code,
  depth,
  indent,
  hasChildren,
  expanded,
  onToggle,
  icon,
}: {
  label: string;
  code?: string;
  depth: number;
  indent: number;
  hasChildren: boolean;
  expanded: boolean;
  onToggle: () => void;
  icon?: ReactNode;
}) {
  return (
    <div
      className={cn(
        'flex cursor-pointer items-center gap-1.5 rounded-md px-2 py-1.5 text-sm transition-colors',
        'hover:bg-muted/60 dark:hover:bg-muted/30',
      )}
      style={{ paddingLeft: `${depth * indent + 8}px` }}
      onClick={onToggle}
    >
      {hasChildren ? (
        expanded ? (
          <ChevronDown className="h-4 w-4 shrink-0 text-muted-foreground" />
        ) : (
          <ChevronRight className="h-4 w-4 shrink-0 text-muted-foreground" />
        )
      ) : (
        <span className="inline-block w-4 shrink-0" />
      )}
      {icon ? <span className="shrink-0">{icon}</span> : null}
      <span className="truncate">{label}</span>
      {code ? (
        <span className="shrink-0 font-mono text-xs text-muted-foreground">{code}</span>
      ) : null}
    </div>
  );
}
