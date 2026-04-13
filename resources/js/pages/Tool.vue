<template>
  <div>
    <Head :title="headingTitle" />

    <Heading class="mb-6">
      {{ headingTitle }}
    </Heading>

    <Card class="p-0">
      <div v-if="selectedWorkflow && selectedWorkflow.description" class="kanban-subtitle">
        {{ selectedWorkflow.description }}
      </div>

      <LoadingView :loading="loading">
        <KanbanBoard
          :tasks="tasks"
          :stages="stages"
          :is-default-workflow="isDefaultWorkflow"
          @transition="handleTransition"
        />
      </LoadingView>
    </Card>
  </div>
</template>

<script>
import KanbanBoard from '../components/KanbanBoard.vue'

const DEFAULT_WORKFLOW_SLUG = 'default'

export default {
  components: {
    KanbanBoard,
  },

  data() {
    return {
      loading: true,
      tasks: [],
      workflows: [],
      selectedWorkflowSlug: DEFAULT_WORKFLOW_SLUG,
    }
  },

  computed: {
    selectedWorkflow() {
      return this.workflows.find(w => w.slug === this.selectedWorkflowSlug) || null
    },

    stages() {
      return this.selectedWorkflow ? this.selectedWorkflow.stages : []
    },

    isDefaultWorkflow() {
      return this.selectedWorkflowSlug === DEFAULT_WORKFLOW_SLUG
    },

    headingTitle() {
      const base = this.__('Tasks')

      if (this.selectedWorkflow) {
        return `${base} — ${this.selectedWorkflow.name}`
      }

      return base
    },
  },

  mounted() {
    this.selectedWorkflowSlug = this.readWorkflowFromUrl() || DEFAULT_WORKFLOW_SLUG
    this.fetchWorkflows()
    window.addEventListener('popstate', this.onPopState)
  },

  beforeUnmount() {
    window.removeEventListener('popstate', this.onPopState)
  },

  methods: {
    readWorkflowFromUrl() {
      const params = new URLSearchParams(window.location.search)
      return params.get('workflow')
    },

    writeWorkflowToUrl(slug, replace = false) {
      const url = new URL(window.location.href)

      if (slug && slug !== DEFAULT_WORKFLOW_SLUG) {
        url.searchParams.set('workflow', slug)
      } else {
        url.searchParams.delete('workflow')
      }

      const method = replace ? 'replaceState' : 'pushState'
      window.history[method]({ slug }, '', url.toString())
    },

    onPopState() {
      const fromUrl = this.readWorkflowFromUrl() || DEFAULT_WORKFLOW_SLUG

      if (fromUrl !== this.selectedWorkflowSlug) {
        this.selectedWorkflowSlug = fromUrl
        this.fetchTasks()
      }
    },

    async fetchWorkflows() {
      try {
        const response = await Nova.request().get('/nova-vendor/opscale-co/nova-service-desk/workflows')
        this.workflows = response.data

        // Validate the selected workflow exists; fallback to default
        if (!this.workflows.some(w => w.slug === this.selectedWorkflowSlug)) {
          this.selectedWorkflowSlug = DEFAULT_WORKFLOW_SLUG
          this.writeWorkflowToUrl(this.selectedWorkflowSlug, true)
        }

        this.fetchTasks()
      } catch (_error) {
        Nova.$emit('error', this.__('Error loading workflows'))
      }
    },

    async fetchTasks() {
      this.loading = true

      try {
        const response = await Nova.request().get('/nova-vendor/opscale-co/nova-service-desk/tasks', {
          params: { workflow: this.selectedWorkflowSlug },
        })
        this.tasks = response.data
      } catch (_error) {
        Nova.$emit('error', this.__('Error loading tasks'))
      } finally {
        this.loading = false
      }
    },

    async handleTransition(event) {
      const { task, stage } = event

      const payload = this.isDefaultWorkflow
        ? { status: stage.id }
        : { stage_id: stage.id }

      try {
        const response = await Nova.request().put(
          `/nova-vendor/opscale-co/nova-service-desk/tasks/${task.id}/transition`,
          payload
        )

        Nova.$emit('success', this.__('Task updated'))

        const updated = response.data.task
        const index = this.tasks.findIndex(t => t.id === task.id)
        if (index !== -1) {
          this.tasks.splice(index, 1, { ...this.tasks[index], ...updated })
        }
      } catch (error) {
        const message = error?.response?.data?.message || this.__('Error updating task')
        Nova.$emit('error', message)
        this.fetchTasks()
      }
    },
  },
}
</script>

<style scoped>
.kanban-subtitle {
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
  font-size: 0.875rem;
  color: #6b7280;
  font-style: italic;
}

.dark .kanban-subtitle {
  border-bottom-color: #374151;
  background: #111827;
  color: #9ca3af;
}
</style>
