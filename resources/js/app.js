/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue");
import { Form, HasError, AlertError } from "vform";
import VueRouter from "vue-router";
import moment from "moment";
import VueProgressBar from "vue-progressbar";
import Swal from "sweetalert2";
import Gate from "./Gate";
Vue.prototype.$Gate = new Gate(window.user);

//globals
window.Fire = new Vue(); //create a custom event
window.Form = Form;
window.Swal = Swal;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.use(VueRouter);

//filters
Vue.filter("upText", function(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
});

Vue.filter("formattedDate", function(date) {
    return moment().format("MMMM Do YYYY", date);
});

//routes
let routes = [
    {
        path: "/dashboard",
        component: require("./components/Dashboard.vue").default
    },
    {
        path: "/profile",
        component: require("./components/Profile.vue").default
    },
    { path: "/users", component: require("./components/Users.vue").default },
    { path: "/developer", component: require("./components/Developer.vue").default },
    { path: "*", component: require("./components/404.vue").default }
];

const router = new VueRouter({
    mode: "history",
    routes, // short for `routes: routes
    linkActiveClass: "active"
});

//progress bar
Vue.use(VueProgressBar, {
    color: "rgb(143, 255, 199)",
    failedColor: "#FF5252",
    height: "20px"
});

//sweetalert
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    onOpen: toast => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
    }
});
window.Toast = Toast;

const Confirm = Swal.mixin({
    customClass: {
        confirmButton: "btn btn-success",
        cancelButton: "btn btn-danger mr-2"
    },
    buttonsStyling: false
});
window.Confirm = Confirm;





//vue components
Vue.component(
    "example-component",
    require("./components/ExampleComponent.vue").default
);

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);
Vue.component(
    'not-found',
    require('./components/404.vue').default
);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: "#app",
    router,
    data:{
        search:""
    }, 
    methods:{
        searchNow:_.debounce(()=>{
            Fire.$emit('searching');
        }, 1000)
    }
});
