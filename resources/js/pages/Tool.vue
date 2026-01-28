<template>
  <div>
    <Head :title="__('Tasks')" />

    <Heading class="mb-6">{{ __('Tasks') }}</Heading>

    <Card class="p-0">
      <LoadingView :loading="loading">
        <KanbanBoard
          :tasks="tasks"
          :statuses="statuses"
          @status-changed="handleStatusChange"
        />
      </LoadingView>
    </Card>
  </div>
</template>

<script>
import KanbanBoard from '../components/KanbanBoard.vue'

export default {
  components: {
    KanbanBoard,
  },

  data() {
    return {
      loading: true,
      tasks: [],
      statuses: [],
      statusIcons: {
        'Open': '📋',
        'In Progress': '🔄',
        'Blocked': '🚫',
        'Resolved': '✅',
        'Closed': '🔒',
        'Cancelled': '❌',
      },
    }
  },

  mounted() {
    this.fetchStatuses()
    this.fetchTasks()
  },

  methods: {
    async fetchStatuses() {
      try {
        const response = await Nova.request().get('/nova-vendor/opscale-co/nova-service-desk/statuses')

        this.statuses = response.data.map(status => ({
          value: status.key,
          label: status.value,
          slug: status.key.toLowerCase().replace(/\s+/g, '-'),
          icon: this.statusIcons[status.key] || '📌',
        }))
      } catch (_error) {
        Nova.$emit('error', this.__('Error loading statuses'))
      }
    },

    async fetchTasks() {
      this.loading = true

      try {
        const response = await Nova.request().get('/nova-vendor/opscale-co/nova-service-desk/tasks')

        this.tasks = response.data.map(task => ({
          id: task.id,
          key: task.key,
          title: task.title,
          status: task.status,
          priority: task.priority,
          dueDate: task.due_date,
        }))
      } catch (_error) {
        Nova.$emit('error', this.__('Error loading tasks'))
      } finally {
        this.loading = false
      }
    },

    async handleStatusChange(event) {
      const { item, column } = event
      const newStatus = column.id

      try {
        await Nova.request().put(`/nova-vendor/opscale-co/nova-service-desk/tasks/${item.id}/status`, {
          status: newStatus,
        })

        Nova.$emit('success', this.__('Task status updated'))

        // Update local task status
        const task = this.tasks.find(t => t.id === item.id)
        if (task) {
          task.status = newStatus
        }
      } catch (_error) {
        Nova.$emit('error', this.__('Error updating task status'))
        // Revert the change by refetching
        this.fetchTasks()
      }
    },
  },
}
</script>

<style scoped>
/* Component-specific styles if needed */
</style>
  