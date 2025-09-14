<!-- AdultsPage.vue -->
<template>
  <div>
    <table class="table">
      <thead v-if="columns.length">
        <tr>
          <th>#</th>
          <th v-for="col in columns" :key="col">{{ headerLabel(col) }}</th>
        </tr>
      </thead>

      <tbody>
        <!-- Datenzeilen -->
        <tr v-for="(book, idx) in books" :key="book.id ?? book.book_id ?? book.isbn ?? idx">
          <td>{{ idx + 1 }}</td>
          <td v-for="col in columns" :key="col">
            {{ cellValue(book, col) }}
          </td>
        </tr>

        <!-- Ladezustand -->
        <tr v-if="loading">
          <td :colspan="columns.length + 1">Lade Bücher…</td>
        </tr>

        <!-- Keine Daten -->
        <tr v-if="!loading && !error && !books.length">
          <td :colspan="columns.length + 1">Keine Einträge gefunden.</td>
        </tr>

        <!-- Fehler -->
        <tr v-if="error">
          <td :colspan="columns.length + 1" style="color:#b00020;">Fehler: {{ error }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Optionales Debugging -->
    <details class="mt-3">
      <summary>Debug</summary>
      <pre style="font-size:12px">{{ books }}</pre>
    </details>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdultsPage',
  data() {
    return {
      books: [],
      loading: false,
      error: '',
      }
  },
  

  computed: {
    columns() {
      if (!this.books.length) return []
      const wanted = [
        'isbn',
        'title',
        'author',
        'age_group',        
        'condition_label',  
        'status_label',    
        'created_at'
      ]
      const first = this.books[0]
      return wanted.filter(k => Object.prototype.hasOwnProperty.call(first, k))
    }
  },

  created() {
    console.log('[AdultsPage] created() läuft');
    this.loadBooks();
  },

  methods: {
    async loadBooks() {
      this.loading = true
      this.error = ''
      try {
        const res = await axios.get('/api/show-books', {
          headers: { Accept: 'application/json' }
        })
        const payload = Array.isArray(res.data)
          ? res.data
          : (res.data?.data ?? [])
        this.books = payload
      } catch (e) {
        this.error = e?.response?.data?.message || e.message || 'Unbekannter Fehler'
        console.error('[AdultsPage] loadBooks failed:', e)
      } finally {
        this.loading = false
      }
    },

    headerLabel(key) {
      const map = {
        isbn: 'ISBN',
        title: 'Titel',
        author: 'Autor',
        age_group: 'Alterskategorie',
        condition_label: 'Zustand',
        status_label: 'Status',
        created_at: 'Hinzugefügt am'
      }
      return map[key] || key
    },

    cellValue(book, col) {
      if (col === 'created_at') return this.formatDate(book[col])
      return book[col] ?? '—'
    },

    formatDate(iso) {
      if (!iso) return '—'
      try {
        return new Intl.DateTimeFormat('de-DE', {
          year: 'numeric', month: '2-digit', day: '2-digit'
        }).format(new Date(iso))
      } catch {
        return iso
      }
    }
  }
}
</script>

<!-- <style lang="scss" src="/resources/css/table.scss"></style> -->

