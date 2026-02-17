<template>
  <div :class="['kanban-item', `status-${statusSlug}`]">
    <div class="kanban-card">
      <div class="kanban-card-header">
        <span class="font-bold text-sm text-gray-700 dark:text-gray-300">
          #{{ task.key }}
        </span>
        <button
          :class="['kanban-priority', `priority-${priorityClass}`]"
          :title="`${__('Priority')}: ${task.priority}`"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 3.5L13 9h-2v7.5L8 11h2V3.5z"/>
          </svg>
        </button>
      </div>

      <h3 class="kanban-card-title">
        {{ task.title }}
      </h3>

      <div class="kanban-card-footer">
        <span v-if="task.dueDate" class="kanban-due-date" :class="{ 'overdue': isOverdue }">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          {{ __('Expires in') }} {{ humanDueDate }}
        </span>
      </div>

      <a
        :href="`/resources/tasks/${task.id}`"
        class="kanban-card-link"
      >
        {{ __('View Details') }}
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KanbanCard',

  props: {
    task: {
      type: Object,
      required: true,
    },
    statusSlug: {
      type: String,
      required: true,
    },
  },

  computed: {
    priorityClass() {
      if (this.task.priority >= 8) return 'high'
      if (this.task.priority >= 5) return 'medium'
      return 'low'
    },

    isOverdue() {
      if (!this.task.dueDate) return false
      return new Date(this.task.dueDate) < new Date()
    },

    humanDueDate() {
      if (!this.task.dueDate) return ''
      const now = new Date()
      const due = new Date(this.task.dueDate)
      const diffMs = due - now
      const absDiffMs = Math.abs(diffMs)
      const minutes = Math.floor(absDiffMs / 60000)
      const hours = Math.floor(absDiffMs / 3600000)
      const days = Math.floor(absDiffMs / 86400000)

      if (days > 0) return `${days} ${this.__('days')}`
      if (hours > 0) return `${hours}h`
      return `${minutes}m`
    },
  },
}
</script>

<style scoped>
.kanban-item {
  margin-bottom: 0.75rem;
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.kanban-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  transition: all 0.2s;
  cursor: move;
}

.dark .kanban-card {
  background: #374151;
  border-color: #4b5563;
}

.kanban-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.kanban-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.kanban-priority {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 0.25rem;
  transition: all 0.2s;
  border: none;
  cursor: help;
}

.priority-high {
  background-color: #fee2e2;
  color: #dc2626;
}

.priority-medium {
  background-color: #fef3c7;
  color: #f59e0b;
}

.priority-low {
  background-color: #dbeafe;
  color: #3b82f6;
}

.kanban-card-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.dark .kanban-card-title {
  color: #f3f4f6;
}

.kanban-card-footer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.kanban-due-date {
  display: flex;
  align-items: center;
  font-size: 0.75rem;
  color: #6b7280;
}

.dark .kanban-due-date {
  color: #9ca3af;
}

.kanban-due-date.overdue {
  color: #dc2626;
  font-weight: 600;
}

.kanban-card-link {
  display: inline-block;
  font-size: 0.75rem;
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.kanban-card-link:hover {
  color: #2563eb;
  text-decoration: underline;
}

.status-open .kanban-card {
  border-left: 4px solid #3b82f6;
}

.status-in-progress .kanban-card {
  border-left: 4px solid #f59e0b;
}

.status-blocked .kanban-card {
  border-left: 4px solid #dc2626;
}

.status-resolved .kanban-card {
  border-left: 4px solid #10b981;
}

.status-closed .kanban-card {
  border-left: 4px solid #6b7280;
}

.status-cancelled .kanban-card {
  border-left: 4px solid #ef4444;
}
</style>
