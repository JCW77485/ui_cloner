<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-800 border-b border-gray-700 px-6 py-4">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">
          <span class="text-blue-400">UI</span> Cloner
        </h1>
        <p class="text-gray-400 text-sm hidden sm:block">
          Clone any website UI into editable Vue components
        </p>
      </div>
    </header>

    <!-- URL Input Bar -->
    <div class="bg-gray-800 border-b border-gray-700 px-6 py-4">
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row gap-3">
        <input
          v-model="url"
          type="url"
          placeholder="Enter website URL (e.g., https://example.com)"
          class="flex-1 bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-400"
          @keyup.enter="cloneUrl"
        />
        <button
          @click="cloneUrl"
          :disabled="isCloning"
          class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-800 disabled:cursor-not-allowed text-white font-medium px-6 py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2"
        >
          <svg v-if="isCloning" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          {{ isCloning ? 'Cloning...' : 'Clone UI' }}
        </button>
        <button
          v-if="clonedHtml"
          @click="convertToVue"
          :disabled="isConverting"
          class="bg-green-600 hover:bg-green-700 disabled:bg-green-800 disabled:cursor-not-allowed text-white font-medium px-6 py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2"
        >
          <svg v-if="isConverting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          {{ isConverting ? 'Converting...' : 'Convert to Vue' }}
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-900/50 border border-red-700 text-red-300 px-6 py-3 mx-6 mt-4 rounded-lg">
      {{ errorMessage }}
    </div>

    <!-- Main Content: Split Screen -->
    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
      <!-- Left Panel: Preview -->
      <div class="lg:w-1/2 flex flex-col border-r border-gray-700">
        <div class="bg-gray-800 px-4 py-2 border-b border-gray-700 flex items-center justify-between">
          <span class="text-sm font-medium text-gray-300">Preview</span>
          <span v-if="clonedHtml" class="text-xs text-gray-500">HTML Preview</span>
        </div>
        <div class="flex-1 overflow-auto bg-white p-4">
          <div v-if="sanitizedHtml" v-html="sanitizedHtml" class="preview-content"></div>
          <div v-else class="flex items-center justify-center h-full text-gray-400">
            <div class="text-center">
              <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
              </svg>
              <p class="text-lg">Enter a URL to preview the cloned UI</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel: Code Editor -->
      <div class="lg:w-1/2 flex flex-col">
        <div class="bg-gray-800 px-4 py-2 border-b border-gray-700 flex items-center justify-between">
          <span class="text-sm font-medium text-gray-300">Code Editor</span>
          <div class="flex gap-2">
            <button
              @click="activeTab = 'html'"
              :class="activeTab === 'html' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1 rounded text-xs font-medium transition-colors"
            >
              HTML
            </button>
            <button
              @click="activeTab = 'vue'"
              :class="activeTab === 'vue' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1 rounded text-xs font-medium transition-colors"
            >
              Vue
            </button>
            <button
              v-if="editorContent"
              @click="copyCode"
              class="text-gray-400 hover:text-white px-3 py-1 rounded text-xs font-medium transition-colors"
            >
              {{ copied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
        </div>
        <div class="flex-1 overflow-hidden">
          <div ref="editorContainer" class="h-full w-full"></div>
          <!-- Fallback textarea if Monaco fails to load -->
          <textarea
            v-if="monacoFailed"
            v-model="editorContent"
            class="w-full h-full bg-gray-900 text-green-400 font-mono text-sm p-4 resize-none focus:outline-none"
            spellcheck="false"
            placeholder="Cloned code will appear here..."
          ></textarea>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import DOMPurify from 'dompurify';

export default {
  name: 'AppComponent',

  data() {
    return {
      url: '',
      clonedHtml: '',
      vueCode: '',
      isCloning: false,
      isConverting: false,
      errorMessage: '',
      activeTab: 'html',
      copied: false,
      monacoEditor: null,
      monacoFailed: false,
      editorContent: '',
    };
  },

  computed: {
    sanitizedHtml() {
      if (!this.clonedHtml) return '';
      return DOMPurify.sanitize(this.clonedHtml, {
        ALLOWED_TAGS: [
          'div', 'span', 'p', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
          'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
          'header', 'footer', 'nav', 'main', 'section', 'article', 'aside',
          'button', 'input', 'label', 'select', 'option', 'textarea',
          'br', 'hr', 'strong', 'em', 'b', 'i', 'u', 'small', 'blockquote',
          'pre', 'code', 'figure', 'figcaption', 'video', 'audio', 'source',
          'svg', 'path', 'circle', 'rect', 'line', 'polyline', 'polygon',
          'g', 'defs', 'use', 'symbol', 'text',
        ],
        ALLOWED_ATTR: [
          'class', 'id', 'href', 'src', 'alt', 'title', 'width', 'height',
          'style', 'type', 'placeholder', 'value', 'name', 'for',
          'target', 'rel', 'role', 'aria-label', 'aria-hidden',
          'viewBox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap',
          'stroke-linejoin', 'd', 'cx', 'cy', 'r', 'x', 'y', 'rx', 'ry',
          'xmlns', 'preserveAspectRatio',
        ],
        ALLOW_DATA_ATTR: false,
      });
    },
  },

  watch: {
    activeTab() {
      this.updateEditorContent();
    },
  },

  mounted() {
    this.initMonaco();
  },

  beforeUnmount() {
    if (this.monacoEditor) {
      this.monacoEditor.dispose();
    }
  },

  methods: {
    async initMonaco() {
      try {
        const monaco = await import('monaco-editor');
        if (this.$refs.editorContainer) {
          this.monacoEditor = monaco.editor.create(this.$refs.editorContainer, {
            value: this.editorContent || '// Cloned code will appear here...',
            language: 'html',
            theme: 'vs-dark',
            automaticLayout: true,
            minimap: { enabled: false },
            fontSize: 14,
            lineNumbers: 'on',
            scrollBeyondLastLine: false,
            wordWrap: 'on',
            tabSize: 2,
          });

          this.monacoEditor.onDidChangeModelContent(() => {
            this.editorContent = this.monacoEditor.getValue();
          });
        }
      } catch (error) {
        console.warn('Monaco editor failed to load, using fallback textarea:', error);
        this.monacoFailed = true;
      }
    },

    async cloneUrl() {
      if (!this.url) {
        this.errorMessage = 'Please enter a valid URL.';
        return;
      }

      this.isCloning = true;
      this.errorMessage = '';
      this.clonedHtml = '';
      this.vueCode = '';

      try {
        const response = await axios.post('/api/clone', {
          url: this.url,
        });

        this.clonedHtml = response.data.html;
        this.activeTab = 'html';
        this.updateEditorContent();
      } catch (error) {
        this.errorMessage = error.response?.data?.error || 'Failed to clone the URL. Please check the URL and try again.';
      } finally {
        this.isCloning = false;
      }
    },

    async convertToVue() {
      if (!this.clonedHtml) {
        this.errorMessage = 'Please clone a URL first.';
        return;
      }

      this.isConverting = true;
      this.errorMessage = '';

      try {
        const response = await axios.post('/api/convert', {
          html: this.clonedHtml,
        });

        this.vueCode = response.data.vue_code;
        this.activeTab = 'vue';
        this.updateEditorContent();
      } catch (error) {
        this.errorMessage = error.response?.data?.error || 'Failed to convert HTML. Please try again.';
      } finally {
        this.isConverting = false;
      }
    },

    updateEditorContent() {
      const content = this.activeTab === 'vue' ? this.vueCode : this.clonedHtml;
      this.editorContent = content || '';

      if (this.monacoEditor) {
        const language = this.activeTab === 'vue' ? 'html' : 'html';
        const model = this.monacoEditor.getModel();
        if (model) {
          model.setValue(this.editorContent);
        }
      }
    },

    async copyCode() {
      try {
        await navigator.clipboard.writeText(this.editorContent);
        this.copied = true;
        setTimeout(() => {
          this.copied = false;
        }, 2000);
      } catch (error) {
        const textarea = document.createElement('textarea');
        textarea.value = this.editorContent;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        this.copied = true;
        setTimeout(() => {
          this.copied = false;
        }, 2000);
      }
    },
  },
};
</script>

<style scoped>
.preview-content {
  all: initial;
  font-family: system-ui, -apple-system, sans-serif;
}

.preview-content * {
  all: revert;
}
</style>
