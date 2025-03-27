<template>
  <AppLayout title="User Management">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        User Management
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="flex justify-between mb-6">
            <Link
              :href="route('admin.users.create')"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
            >
              Add New User
            </Link>
          </div>

          <div v-if="$page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $page.props.flash.success }}
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users" :key="user.id" :class="{'bg-red-50': user.deleted_at}">
                  <td class="px-6 py-4 whitespace-nowrap">{{ user.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-for="role in user.roles" :key="role.id" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                      {{ role.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="user.deleted_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                      Deleted
                    </span>
                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
                      <template v-if="!user.deleted_at">
                        <Link
                          :href="route('admin.users.edit', user.id)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Edit
                        </Link>
                        <button
                          @click="destroy(user.id)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </template>
                      <button
                        v-else
                        @click="restore(user.id)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Restore
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  users: Array,
});

const form = useForm({});

const destroy = (id) => {
  if (confirm('Are you sure you want to delete this user?')) {
    form.delete(route('admin.users.destroy', id));
  }
};

const restore = (id) => {
  form.put(route('admin.users.restore', id));
};
</script>
