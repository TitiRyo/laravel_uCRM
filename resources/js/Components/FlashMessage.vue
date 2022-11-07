<script setup>
</script>

<template>
    <div v-if="$page.props.flash.status === 'success'" class="p-4 bg-blue-200 text-white">{{$page.props.flash.message}}</div>
    <div v-if="$page.props.flash.status === 'danger'" class="p-4 bg-red-200 text-white">{{$page.props.flash.message}}</div>
</template>