// stores/templateStore.js
import { defineStore } from 'pinia';
import axios from 'axios';
import globalValue from "../mixins/helper.js";

const getMixinValue = globalValue();

export const useTemplateStore = defineStore('templateStore', {
  state: () => ({
    templates: [],
    loading: false,
    error: null,
    currentPage: 1,
    lastPage: 1,
    searchQuery: '',
  }),
  actions: {
    async fetchTemplates(page = 1, searchQuery = '') {
      this.loading = true;
      this.currentPage = page;
      this.searchQuery = searchQuery;

      try {
        const url = getMixinValue.getUrl("whatsapp-templates?flow_builder=1");
        const response = await axios.get(url, {
          params: {
            page: this.currentPage,
            search: this.searchQuery,
          },
        });
        this.templates = response.data.templates;
        this.lastPage = response.data.last_page;
      } catch (error) {
        this.error = error;
      } finally {
        this.loading = false;
      }
    },
    
    async loadMoreTemplates() {
      if (this.currentPage < this.lastPage) {
        this.currentPage++;
        try {
          const url = getMixinValue.getUrl("whatsapp-templates");
          const response = await axios.get(url, {
            params: {
              page: this.currentPage,
              search: this.searchQuery,
            },
          });

          this.templates = [...this.templates, ...response.data.data];
        } catch (error) {
          this.error = error;
        }
      }
    },
    async searchTemplates(query) {
      this.currentPage = 1;
      this.templates = [];
      await this.fetchTemplates(1, query);
    },
    async fetchTemplate(id) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/templates/${id}`);
        this.template = response.data;
      } catch (error) {
        this.error = error;
      } finally {
        this.loading = false;
      }
    },
  },
});
