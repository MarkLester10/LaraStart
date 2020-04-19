<template>
  <div class="container">
    <div class="row mt-5" v-if="$Gate.isAdminOrAuthor()">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Users</h3>

            <div class="card-tools">
              <button class="btn btn-success" @click="newModal">
                <i class="fas fa-user-plus"></i>
                Add New
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Type</th>
                  <th>Registered At</th>
                  <th>Modify</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(user, index) in users.data" :key="user.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.type | upText }}</td>
                  <td>{{ user.created_at | formattedDate }}</td>
                  <td>
                    <a href="#" class="mr-3" @click.prevent="editModal(user)">
                      <i class="fa fa-edit green"></i>
                    </a>
                    <a href="#" @click="deleteUser(user.id)">
                      <i class="fa fa-trash red"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->

          <div class="card-footer">
            <pagination :data="users" @pagination-change-page="getResults"></pagination>
          </div>
        </div>
        <!-- /.card -->
      </div>
    </div>

      <div v-if="!$Gate.isAdminOrAuthor()">
         <not-found></not-found>
      </div>

    <!-- Modal -->
    <div
      class="modal fade"
      id="addNewModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="addNewModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header" :class="editMode? 'bg-success' : 'bg-primary'">
            <h5 class="modal-title" id="addNewModalLabel">{{editMode ?'Edit User Information': 'Add New'}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form @submit.prevent="editMode ? updateUser() : createUser()">
            <div class="modal-body">
              <div class="form-group">
                <input
                  v-model="form.name"
                  type="text"
                  name="name"
                  id="name"
                  placeholder="Name"
                  class="form-control"
                  :class="{
                                        'is-invalid': form.errors.has('name')
                                    }"
                />
                <has-error :form="form" field="name"></has-error>
              </div>

              <div class="form-group">
                <input
                  v-model="form.email"
                  type="email"
                  id="email"
                  name="email"
                  placeholder="Email"
                  class="form-control"
                  :class="{'is-invalid': form.errors.has('email')}"
                />
                <has-error :form="form" field="email"></has-error>
              </div>

              <div class="form-group">
                <textarea
                  v-model="form.bio"
                  name="bio"
                  id="bio"
                  placeholder="Short bio (optional)"
                  class="form-control"
                  :class="{'is-invalid': form.errors.has('bio')}"
                ></textarea>
                <has-error :form="form" field="bio"></has-error>
              </div>

              <div class="form-group">
                <select
                  v-model="form.type"
                  id="type"
                  name="type"
                  class="form-control"
                  :class="{
                                        'is-invalid': form.errors.has('type')
                                    }"
                >
                  <option value>Select user role</option>
                  <option value="admin">Admin</option>
                  <option value="author">Author</option>
                  <option value="user">User</option>
                </select>
                <has-error :form="form" field="type"></has-error>
              </div>

              <div class="form-group">
                <input
                  v-model="form.password"
                  type="password"
                  id="password"
                  name="password"
                  placeholder="Password"
                  class="form-control"
                  :class="{'is-invalid': form.errors.has('password')}"
                />
                <has-error :form="form" field="password"></has-error>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button v-show="editMode" type="submit" class="btn btn-success">Update</button>
              <button v-show="!editMode" type="submit" class="btn btn-primary">Create</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  
  </div>
</template>

<script>
export default {
  data() {
    return {
      editMode: false,
      users: {},
      form: new Form({
        id:'',
        name: "",
        email: "",
        password: "",
        type: "",
        bio: "",
        photo: ""
      })
    };
  },
  methods: {
       getResults(page=1){
         axios.get('api/user?page=' + page)
				.then(response => {
					this.users = response.data;
				});
       },
    editModal(user){
      this.editMode = true;
      this.form.reset();
       $("#addNewModal").modal("show");
       this.form.fill(user);
    },
    updateUser(){
      this.$Progress.start();
      this.form.put('api/user/'+ this.form.id)
      .then(()=>{
        $("#addNewModal").modal("hide");
        this.loadUsers();
         Toast.fire({
            icon: "success",
            title: "User has been updated!"
          });
        this.$Progress.finish();
      })
      .catch(()=>{
        this.$Progress.fail();
      })
    },
    newModal(){
      this.editMode = false;
      this.form.clear();
      this.form.reset();
       $("#addNewModal").modal("show");
    },
    deleteUser(id) {
      Confirm.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          this.$Progress.start();
          this.form
            .delete("api/user/" + id)
            .then(() => {
              this.loadUsers();
              Toast.fire({
                icon: "success",
                title: "User has been deleted!"
              });
              this.$Progress.finish();
            })
            .catch((error) => {
              this.$Progress.fail();
              Confirm.fire("Failed!", "You're not authorized", "error");
            });
        } else if (
          /* Read more about handling dismissals below */
          result.dismiss === Swal.DismissReason.cancel
        ) {
          Toast.fire({
            icon: "info",
            title: "User profile is safe :)"
          });
        }
      });
    },
    createUser() {
      this.$Progress.start();
      this.form
        .post("api/user")
        .then(() => {
          $("#addNewModal").modal("hide");
          //   Fire.$emit("AfterCreate");
          this.loadUsers();
          Toast.fire({
            icon: "success",
            title: "User Created Successfully"
          });
          this.$Progress.finish();
        })
        .catch(() => this.$Progress.fail());
    },

    loadUsers() {
      if(this.$Gate.isAdminOrAuthor()){
        axios.get("api/user").then(({ data }) => (this.users = data));
      }
    }
  },

  mounted() {
    Fire.$on('searching', ()=>{
      let query=this.$parent.search;
      if(query.length < 1){
        this.loadUsers();
      }else{
      axios.get('api/findUser?q=' + query)
      .then((data)=>{
        this.users = data.data;
      })
      .catch(()=>{
        Toast.fire({
            icon: "error",
            title: "Something went wrong"
          });
      });
      }
     
    });
    this.loadUsers();
    // Fire.$on("AfterCreate", () => this.loadUsers());
  }
}
</script>
