<template>
  <div class="kanban-board">
    <div class="kanban-columns">
      <div
        v-for="column in columns"
        :key="column.id"
        class="kanban-column"
        :class="`column-color-${column.color}`"
      >
        <div class="kanban-column-header">
          <h3 class="kanban-column-title">
            <span class="kanban-column-icon">{{ column.icon }}</span>
            {{ column.title }}
          </h3>
          <span class="kanban-column-count">{{ column.items.length }} {{ __('tasks') }}</span>
        </div>
        <div
          class="kanban-column-body"
          @drop="onDrop($event, column)"
          @dragover="onDragOver"
          @dragenter="onDragEnter($event)"
          @dragleave="onDragLeave($event)"
        >
          <div
            v-for="item in column.items"
            :key="item.id"
            draggable="true"
            @dragstart="onDragStart($event, item)"
            @dragend="onDragEnd"
          >
            <KanbanCard :task="item" :status-slug="column.slug" />
          </div>
          <div v-if="column.items.length === 0" class="kanban-empty-state">
            {{ __('No tasks') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import KanbanCard from './KanbanCard.vue'

const STAGE_ICONS = {
  Open: '📋',
  'In Progress': '🔄',
  Blocked: '🚫',
  Resolved: '✅',
  Closed: '🔒',
  Cancelled: '❌',
}

export default {
  name: 'KanbanBoard',

  components: {
    KanbanCard,
  },

  props: {
    tasks: {
      type: Array,
      required: true,
    },
    stages: {
      type: Array,
      required: true,
    },
    isDefaultWorkflow: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['transition'],

  data() {
    return {
      draggedItem: null,
    }
  },

  computed: {
    columns() {
      return this.stages.map(stage => {
        const items = this.tasks.filter(task => this.taskBelongsToStage(task, stage))

        return {
          id: stage.id,
          title: stage.name,
          color: stage.color || 'info',
          icon: STAGE_ICONS[stage.maps_to_status] || STAGE_ICONS[stage.name] || '📌',
          slug: this.slugify(stage.name),
          items,
        }
      })
    },
  },

  methods: {
    /**
     * Determine if a task belongs to a given column.
     * - Default workflow: match by master `status` value (stage.id is the TaskStatus value)
     * - Custom workflow: match by `workflow_stage_id` (stage.id is the WorkflowStage ULID)
     */
    taskBelongsToStage(task, stage) {
      if (this.isDefaultWorkflow) {
        return task.status === stage.id
      }

      return task.workflow_stage_id === stage.id
    },

    slugify(value) {
      return String(value || '')
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-]/g, '')
    },

    onDragStart(event, item) {
      this.draggedItem = item
      event.dataTransfer.effectAllowed = 'move'
      event.dataTransfer.setData('text/plain', item.id)
      event.target.style.opacity = '0.5'
    },

    onDragEnd(event) {
      event.target.style.opacity = '1'
      this.draggedItem = null
    },

    onDragOver(event) {
      event.preventDefault()
      event.dataTransfer.dropEffect = 'move'
    },

    onDragEnter(event) {
      if (event.target.classList.contains('kanban-column-body')) {
        event.target.classList.add('drag-over')
      }
    },

    onDragLeave(event) {
      if (event.target.classList.contains('kanban-column-body')) {
        event.target.classList.remove('drag-over')
      }
    },

    onDrop(event, column) {
      event.preventDefault()

      if (event.target.classList.contains('kanban-column-body')) {
        event.target.classList.remove('drag-over')
      }

      if (!this.draggedItem) {
        return
      }

      const currentColumnId = this.isDefaultWorkflow
        ? this.draggedItem.status
        : this.draggedItem.workflow_stage_id

      if (currentColumnId === column.id) {
        return
      }

      this.$emit('transition', {
        task: this.draggedItem,
        stage: { id: column.id, name: column.title },
      })
    },
  },
}
</script>

<style scoped>
.kanban-board {
  padding: 1rem;
  overflow-x: auto;
}

.kanban-columns {
  display: flex;
  gap: 1rem;
  min-height: 100vh;
}

.kanban-column {
  min-width: 280px;
  max-width: 320px;
  background: #f9fafb;
  border-radius: 0.5rem;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  border-top: 4px solid #9ca3af;
}

.dark .kanban-column {
  background: #1f2937;
}

.column-color-info {
  border-top-color: #3b82f6;
}

.column-color-warning {
  border-top-color: #f59e0b;
}

.column-color-danger {
  border-top-color: #dc2626;
}

.column-color-success {
  border-top-color: #10b981;
}

.kanban-column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e5e7eb;
}

.dark .kanban-column-header {
  border-bottom-color: #374151;
}

.kanban-column-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.dark .kanban-column-title {
  color: #f3f4f6;
}

.kanban-column-icon {
  font-size: 1rem;
}

.kanban-column-count {
  font-size: 0.75rem;
  color: #6b7280;
  background: #e5e7eb;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.dark .kanban-column-count {
  background: #374151;
  color: #9ca3af;
}

.kanban-column-body {
  flex: 1;
  overflow-y: auto;
  min-height: 100px;
}

.kanban-empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  color: #9ca3af;
  font-size: 0.875rem;
  text-align: center;
}

.dark .kanban-empty-state {
  color: #6b7280;
}

.kanban-column-body > div[draggable="true"] {
  cursor: move;
  margin-bottom: 0.75rem;
}

.kanban-column-body.drag-over {
  background-color: rgba(59, 130, 246, 0.1);
  border: 2px dashed #3b82f6;
}

.dark .kanban-column-body.drag-over {
  background-color: rgba(59, 130, 246, 0.2);
}
</style>
