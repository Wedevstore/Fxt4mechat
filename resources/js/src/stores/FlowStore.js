import { defineStore } from 'pinia';
import FlowService from '../services/FlowService';
export const useFlowStore = defineStore('flow', {
  state: () => ({
    flows: [],
    selectedFlow: null,
    error: null,
    loading: false,
    pagination: {
      currentPage: 1,
      totalPages: 1,
      perPage: 10,
    },
    searchQuery: '',
  }),

  getters: {
    allFlows: (state) => state.flows,
    isLoading: (state) => state.loading,
    hasError: (state) => state.error !== null,
  },
  actions: {
    // Fetch flows with pagination and search
    fetchFlows({ page = 1, searchQuery = '' }) {
      this.loading = true;
      this.searchQuery = searchQuery;
      return FlowService.getFlows(page, searchQuery)
        .then((response) => {
         console.log(response.data.flows.data);
          this.flows = response.data.flows;
          this.pagination.currentPage = response.data.current_page;
          this.pagination.totalPages = response.data.last_page;
          this.error = null;
          return Promise.resolve(response);
        })
        .catch((error) => {
          this.error = error;
          return Promise.reject(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    // Add a new flow
    addFlow(flowData) {
      return FlowService.addFlow(flowData)
        .then((response) => {
          this.flows.push(response.data);
          return Promise.resolve(response);
        })
        .catch((error) => {
          this.error = error;
          return Promise.reject(error);
        });
    },

    // Update an existing flow
    updateFlow(flowData) {
      return FlowService.updateFlow(flowData)
        .then((response) => {
          const index = this.flows.findIndex((f) => f.id === flowData.id);
          if (index !== -1) {
            this.flows[index] = response.data;
          }
          return Promise.resolve(response);
        })
        .catch((error) => {
          this.error = error;
          return Promise.reject(error);
        });
    },

    // Delete a flow
    deleteFlow(flowId) {
      return FlowService.deleteFlow(flowId)
        .then(() => {
          this.flows = this.flows.filter((flow) => flow.id !== flowId);
          return Promise.resolve();
        })
        .catch((error) => {
          this.error = error;
          return Promise.reject(error);
        });
    },

    // Fetch a single flow by ID
    fetchFlowById(flowId) {
      this.loading = true;
      return FlowService.getFlowById(flowId)
        .then((response) => {
          this.selectedFlow = response.data;
          return Promise.resolve(response);
        })
        .catch((error) => {
          this.error = error;
          return Promise.reject(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    // Set the current page for pagination
    setCurrentPage(page) {
      this.pagination.currentPage = page;
      this.fetchFlows({ page, searchQuery: this.searchQuery });
    },

    // Set the search query and fetch flows based on it
    setSearchQuery(query) {
      this.searchQuery = query;
      this.fetchFlows({ page: 1, searchQuery: query });
    },
  },
});
