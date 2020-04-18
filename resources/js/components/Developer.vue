<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12" v-if="$Gate.isAdmin()">
        <passport-clients></passport-clients>
        <passport-authorized-clients></passport-authorized-clients>
        <passport-personal-access-tokens></passport-personal-access-tokens>
      </div>
      
      <div v-if="!$Gate.isAdmin()">
         <not-found></not-found>
      </div>
      
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    console.log("Component mounted.");
  }
};
</script>
