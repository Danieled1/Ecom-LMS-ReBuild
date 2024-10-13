import { setupEditStatusEventListeners, setupSaveStatusEventListeners } from './eventHandlers.js';

export function initStatusManagement() {
  setupEditStatusEventListeners();
  setupSaveStatusEventListeners();
}
