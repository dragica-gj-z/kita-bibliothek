
<template>
  <div class="add-books-page" style="max-width: 920px; margin: 2rem auto;">
    <h1 style="margin-bottom: 1rem;">Neues Buch hinzufügen</h1>

    <form @submit.prevent="submitForm" style="display: grid; gap: 1rem;">
      <!-- ISBN + Auto-ausfüllen -->
      <div class="mb-3">
        <label for="isbn" class="form-label">ISBN</label>
        <div style="display:flex; gap:.5rem; align-items:center;">
          <input
            id="isbn" class="form-control"
            v-model.trim="form.isbn" type="text" required
            placeholder="z.B. 978-3-16-148410-0"
          />
          <button type="button" class="btn btn-outline-secondary"
                  :disabled="autofillLoading || !form.isbn"
                  @click="autofillFromIsbn"
                  title="Füllt alle Felder per ISBN">
            {{ autofillLoading ? 'Lade…' : 'Mit KI autoausfühlen' }}
          </button>
        </div>
        <small v-if="errors.isbn" style="color:#c00">{{ errors.isbn }}</small>
        <small v-if="autofillError" style="color:#c00">{{ autofillError }}</small>
        <small v-if="autofillHint" style="color:#090">{{ autofillHint }}</small>
      </div>

      <!-- Titel / Autor -->
      <div class="mb-3">
        <label for="title" class="form-label">Titel</label>
        <input id="title" class="form-control" v-model.trim="form.title" type="text" required />
        <small v-if="errors.title" style="color:#c00">{{ errors.title }}</small>
      </div>

      <div class="mb-3">
        <label for="author" class="form-label">Autor:in</label>
        <input id="author" class="form-control" v-model.trim="form.author" type="text" required />
        <small v-if="errors.author" style="color:#c00">{{ errors.author }}</small>
      </div>

      <!-- Publisher / Erscheinungsdatum / Seiten / Kategorien -->
      <div class="mb-3">
        <label for="publisher" class="form-label">Verlag</label>
        <input id="publisher" class="form-control" v-model.trim="form.publisher" type="text" />
        <small v-if="errors.publisher" style="color:#c00">{{ errors.publisher }}</small>
      </div>

      <div class="mb-3">
        <label for="publishedAt" class="form-label">Erscheinungsdatum</label>
        <input id="publishedAt" class="form-control" v-model.trim="form.publishedAt" type="text" placeholder="z.B. 2019-05-12 oder 2019" />
        <small v-if="errors.publishedAt" style="color:#c00">{{ errors.publishedAt }}</small>
      </div>

      <div class="mb-3">
        <label for="pageCount" class="form-label">Seitenzahl</label>
        <input id="pageCount" class="form-control" v-model.number="form.pageCount" type="number" min="1" placeholder="z.B. 32" />
        <small v-if="errors.pageCount" style="color:#c00">{{ errors.pageCount }}</small>
      </div>

      <div class="mb-3">
        <label for="categories" class="form-label">Kategorien (Kommagetrennt)</label>
        <input id="categories" class="form-control"
               v-model.trim="form.categories" type="text" placeholder="z.B. Bilderbuch, Tiere" />
        <small v-if="errors.categories" style="color:#c00">{{ errors.categories }}</small>
      </div>

      <!-- Cover-URL + Vorschau -->
      <div class="mb-3">
        <label for="cover" class="form-label">Cover-URL</label>
        <input id="cover" class="form-control"
               v-model.trim="form.cover" type="url" placeholder="https://…" />
        <small v-if="errors.cover" style="color:#c00">{{ errors.cover }}</small>
        <div v-if="form.cover" style="margin-top:.5rem;">
          <img :src="form.cover" alt="Cover-Vorschau" style="max-height: 180px; border:1px solid #ddd; border-radius:6px;" @error="onCoverError" />
        </div>
      </div>

      <!-- Beschreibung -->
      <div class="mb-3">
        <label for="description" class="form-label">Beschreibung</label>
        <textarea id="description" class="form-control"
                  v-model.trim="form.description" rows="4"
                  placeholder="Kurzbeschreibung..."></textarea>
        <small v-if="errors.description" style="color:#c00">{{ errors.description }}</small>
      </div>

      <!-- Vertrauenswürdigkeit (confidence) -->
       <div class="mb-3">
        <label class="form-label">Vertrauenswürdigkeit</label>
        <div v-if="form.confidenceLabel">
          <span class="badge" :class="'confidence-' + form.confidence">
            {{ form.confidenceLabel }}
          </span>
        </div>
        <div v-else>
          <small>Wird nach dem Auto-Ausfüllen von der KI gesetzt.</small>
        </div>
      </div>

      <!-- Alterskategorie -->
      <div class="mb-3">
        <label for="category_per_age" class="form-label">Alterskategorie</label>
        <input id="category_per_age" class="form-control"
               v-model="form.category_per_age" type="text" placeholder="z.B. 3-6 Jahre" />
        <small v-if="errors.category_per_age" style="color:#c00">{{ errors.category_per_age }}</small>
      </div>

      <!-- Status / Zustand -->
      <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select id="status" class="form-select" v-model="form.status" required>
          <option disabled value="">-- bitte auswählen --</option>
          <option value="available">Verfügbar</option>
          <option value="borrowed">Ausgeliehen</option>
          <option value="reserved">Reserviert</option>
          <option value="lost">Verloren</option>
        </select>
        <small v-if="errors.status" style="color:#c00">{{ errors.status }}</small>
      </div>

      <div class="mb-3">
        <label for="condition" class="form-label">Zustand</label>
        <select id="condition" class="form-select" v-model="form.condition" required>
          <option value="new">Neu</option>
          <option value="used">Gebraucht</option>
          <option value="damaged">Beschädigt</option>
          <option value="missing_pages">Seiten fehlen</option>
          <option value="repaired">Repariert</option>
        </select>
        <small v-if="errors.condition" style="color:#c00">{{ errors.condition }}</small>
      </div>

      <!-- Aktionen -->
      <div style="display:flex; gap:.75rem; align-items:center;">
        <button class="btn btn-dark" type="submit" :disabled="loading">
          {{ loading ? 'Speichern...' : 'Buch anlegen' }}
        </button>
        <span v-if="successMessage" style="color:#0a0">{{ successMessage }}</span>
        <span v-if="serverError" style="color:#c00">{{ serverError }}</span>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AddBooks',
  data() {
    return {
      form: {
        isbn: '',
        title: '',
        author: '',
        description: '',
        confidence: null,        
        confidenceLabel: '',
        publisher: '',
        publishedAt: '',
        pageCount: '',
        categories: '', 
        cover: '',
        category_per_age: '',
        status: '',
        condition: '',
      },
      loading: false,
      errors: {},
      successMessage: '',
      serverError: '',
      autofillLoading: false,
      autofillError: '',
      autofillHint: '',
    }
  },
 created() {
    this.loadConfidenceOptions();  
  },

  methods: {
    onCoverError(e) {
      // Bild-Fehler ignorieren und kleine Info geben
      this.autofillHint = ''
      this.autofillError = 'Cover konnte nicht geladen werden. URL prüfen.'
    },

    async autofillFromIsbn() {
      if (!this.form.isbn || this.form.isbn.replace(/[^0-9Xx]/g, '').length < 10) {
        this.autofillError = 'Bitte eine gültige ISBN (10/13 Stellen) eingeben.'
        return
      }
      this.autofillLoading = true
      this.autofillError = ''
      this.autofillHint = ''
      try {
        const res = await axios.post('/books/autofill', {
          isbn: this.form.isbn,
        })
        const j = res.data

        if (j.title) this.form.title = j.title
        if (Array.isArray(j.authors) && j.authors.length) {
          this.form.author = j.authors.join(', ')
        }
        if (j.description) this.form.description = j.description
        if (j.confidence) this.form.confidence = j.confidence
        if (j.confidence_label) this.form.confidenceLabel = j.confidence_label
        if (j.publisher) this.form.publisher = j.publisher
        if (j.publishedAt) this.form.publishedAt = j.publishedAt
        if (j.pageCount != null) this.form.pageCount = j.pageCount
        if (Array.isArray(j.categories) && j.categories.length) {
          this.form.categories = j.categories.join(', ')
        }
        if (j.cover) this.form.cover = j.cover

        if (j.age && (Number.isInteger(j.age.min) || Number.isInteger(j.age.max))) {
          const min = Number.isInteger(j.age.min) ? j.age.min : '?'
          const max = Number.isInteger(j.age.max) ? j.age.max : '?'
          this.form.category_per_age = `${min}-${max} Jahre`
        }

        this.autofillHint = 'Daten automatisch eingefüllt. Bitte prüfen und ggf. anpassen.'
      } catch (err) {
        this.autofillError =
          err?.response?.data?.error ||
          err?.response?.data?.message || 'Auto-Ausfüllen fehlgeschlagen. Prüfe die ISBN.'
      } finally {
        this.autofillLoading = false
      }
    },

    async submitForm() {
      this.loading = true
      this.successMessage = ''
      this.serverError = ''
      this.errors = {}

      try {
        const formData = {
          isbn: this.form.isbn?.trim() || null,
          title: this.form.title?.trim() || null,
          author: this.form.author?.trim() || null,
          description: this.form.description?.trim() || null,
          confidence: this.form.confidence || null,
          status: this.form.status || null,
          condition: this.form.condition || null,
          category_per_age: this.form.category_per_age || null,
          publisher: this.form.publisher?.trim() || null,
          publishedAt: this.form.publishedAt?.trim() || null,
          pageCount: this.form.pageCount || null,
          categories: this.form.categories?.trim() || null,
          cover: this.form.cover?.trim() || null,
        }

        const res = await axios.post('/api/add-book', formData)

        this.serverError = ''
        this.successMessage = res.data?.message || 'Buch erfolgreich angelegt.'
        // Reset
        this.form = {
          isbn: '', 
          title: '', 
          author: '', 
          description: '',
          confidence: null,
          confidenceLabel: null,
          publisher: '', 
          publishedAt: '', 
          pageCount: '',
          categories: '', 
          cover: '',
          category_per_age: '', 
          status: '', 
          condition: '',
        }
        this.autofillHint = ''
        this.autofillError = ''
      } catch (err) {
        if (err?.response?.status === 422 && err.response.data?.errors) {
          const v = err.response.data.errors
          const e = {}
          for (const k in v) e[k] = Array.isArray(v[k]) ? v[k][0] : String(v[k])
          this.errors = e
        } else {
          this.serverError = err?.response?.data?.message || 'Es ist ein Fehler aufgetreten.'
        }
      } finally {
        this.loading = false
      }
    },

    async loadConfidenceOptions() {
  this.loadingConfidence = true
  try {
    const res = await axios.get('/api/meta/confidence')

    // res.data ist direkt ein Array
    this.confidenceOptions = Array.isArray(res.data) ? res.data : []

    if (!this.form.confidence && this.confidenceOptions.length > 0) {
      this.form.confidence = this.confidenceOptions[0].value
    }
  } catch (err) {
    console.error('Fehler beim Laden der Vertrauenswürdigkeitsoptionen:', err)

    // Fallback
    this.confidenceOptions = [
      { value: 'high',   label: 'hoch' },
      { value: 'medium', label: 'mittel' },
      { value: 'low',    label: 'niedrig' },
    ]
    if (!this.form.confidence) {
      this.form.confidence = 'high'
    }
  } finally {
    this.loadingConfidence = false
  }
},

  },
}
</script>

<style scoped>
.form-label { font-weight: 600; }
.form-control, .form-select, textarea { width: 100%; }
button[disabled] { opacity: .6; cursor: not-allowed; }

.confidence-high { background: #56ef79; }
.confidence-medium { background: #f9d765; }
.confidence-low { background: #f13c4b; }

</style>
