<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    required: false,
  },
  operationType: {
    type: String,
    default: "insert", // insert / update
  },
  projects: {
    type: Array,
    required: true,
  },
  fixedProject: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(["save", "cancel"]);

const editingOrder = ref(props.order);

watch(
  () => props.order,
  (newOrder) => {
    editingOrder.value = newOrder;
  }
);

watch(
  () => props.fixedProject,
  (newFixedProject) => {
    if (newFixedProject) {
      editingOrder.value.project_id = newFixedProject;
    }
  },
  { immediate: true }
);

const orderTittle = computed(() => {
  if (!editingOrder.value) {
    return "";
  }
  return props.operationType == "insert" ? "New Order" : "Order #" + editingOrder.value.id;
});

const save = () => {
  emit("save", editingOrder.value);
};

const cancel = () => {
  emit("cancel", editingOrder.value);
};
</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
    <h3 class="mt-5 mb-3">{{ orderTittle }}</h3>
    <hr />

    <div class="d-flex flex-wrap justify-content-between">
      <div class="mb-3 checkCompleted">
        <div class="form-check">
          <input
            class="form-check-input"
            type="checkbox"
            v-model="editingOrder.completed"
            id="inputCompleted"
          />
          <label class="form-check-label" for="inputCompleted"> Order is Completed </label>
          <field-error-message
            :errors="errors"
            fieldName="completed"
          ></field-error-message>
        </div>
      </div>
      <div class="row mb-3 total_hours" v-show="editingOrder.completed">
        <label for="inputHours" class="col-sm-2 col-form-label">Hours</label>
        <div class="col-sm-10">
          <input
            type="number"
            class="form-control"
            id="inputHours"
            placeholder="Total hours to complete the task"
            v-model="editingOrder.total_hours"
          />
          <field-error-message
            :errors="errors"
            fieldName="total_hours"
          ></field-error-message>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <label for="inputDescription" class="form-label">Description</label>
      <input
        type="text"
        class="form-control"
        id="inputDescription"
        placeholder="Task Description"
        required
        v-model="editingOrder.description"
      />
      <field-error-message :errors="errors" fieldName="description"></field-error-message>
    </div>
    <div class="mb-3">
      <label for="inputProject" class="form-label">Project</label>
      <select
        class="form-select"
        id="inputProject"
        :disabled="fixedProject"
        v-model="editingOrder.project_id"
      >
        <option :value="null">-- No Project --</option>
        <option v-for="prj in projects" :key="prj.id" :value="prj.id">
          {{ prj.name }}
        </option>
      </select>
      <field-error-message :errors="errors" fieldName="project_id"></field-error-message>
    </div>
    <div class="mb-3">
      <label for="inputNotes" class="form-label">Notes</label>
      <textarea
        class="form-control"
        id="inputNotes"
        rows="4"
        v-model="editingOrder.notes"
      ></textarea>
      <field-error-message :errors="errors" fieldName="notes"></field-error-message>
    </div>

    <div class="mb-3 d-flex justify-content-end">
      <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
      <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
    </div>
  </form>
</template>

<style scoped>
.total_hours {
  width: 26rem;
}
.checkCompleted {
  min-height: 2.375rem;
}
</style>
