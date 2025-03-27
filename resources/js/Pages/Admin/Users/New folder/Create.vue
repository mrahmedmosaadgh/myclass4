<template>
  <div class="container">
    <h1>Add New User</h1>
    <form @submit.prevent="submit">
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input v-model="data.name" type="text" id="name" class="form-control">
        <div v-if="errors.name" class="text-danger">{{ errors.name }}</div>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input v-model="data.email" type="email" id="email" class="form-control">
        <div v-if="errors.email" class="text-danger">{{ errors.email }}</div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input v-model="data.password" type="password" id="password" class="form-control">
        <div v-if="errors.password" class="text-danger">{{ errors.password }}</div>
      </div>
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input v-model="data.password_confirmation" type="password" id="password_confirmation" class="form-control">
      </div>
      <button type="submit" class="btn btn-success">Create User</button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from  '@inertiajs/vue3'; 

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('admin.users.store'));
};

const { data, errors } = form;
</script>
