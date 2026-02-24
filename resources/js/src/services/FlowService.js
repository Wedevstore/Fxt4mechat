import axios from 'axios';
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();

class FlowService {
  // Fetch flows with pagination and search
  getFlows(page = 1, searchQuery = '') {
    let url = getMixinValue.getUrl("flow-builder/list");
    return axios.get(url, {
      params: {
        page,
        search: searchQuery,
      },
    });
  }

  // Add a new flow
  addFlow(flowData) {
    return axios.post('/api/flows', flowData);
  }

  // Update an existing flow
  updateFlow(flowData) {
    return axios.put(`/api/flows/${flowData.id}`, flowData);
  }

  // Delete a flow
  deleteFlow(flowId) {
    return axios.delete(`/api/flows/${flowId}`);
  }

  // Fetch a single flow by ID
  getFlowById(flowId) {
    return axios.get(`/api/flows/${flowId}`);
  }
};

export default new FlowService();

