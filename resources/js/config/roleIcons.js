/**
 * Иконки и стили для ролей участников задачи.
 * Меняй здесь — изменится везде.
 */

import { Settings, Eye, User } from 'lucide-vue-next'

export const ROLE_ICONS = {
  creator: { label: 'Создатель', icon: User },
  assignee: { label: 'Исполнители', icon: Settings },
  watcher: { label: 'Наблюдатели', icon: Eye },
}
