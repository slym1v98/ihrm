export interface NotificationMessage {
  id: string;
  template_code: string;
  channel: string;
  subject_rendered: string;
  body_rendered: string;
  status: string;
  read_at: string | null;
  created_at: string | null;
}
