import{i as u,A as i,o as t,f as s,b as c,t as d,r as m,G as p,k as _,p as f,H as g}from"./app-DoGou6e8.js";const k={class:"text-sm text-red-600 dark:text-red-400"},S={__name:"InputError",props:{message:{type:String}},setup(e){return(a,r)=>u((t(),s("div",null,[c("p",k,d(e.message),1)],512)),[[i,e.message]])}},x={class:"block text-sm font-medium text-gray-700 dark:text-gray-300"},y={key:0},v={key:1},V={__name:"InputLabel",props:{value:{type:String}},setup(e){return(a,r)=>(t(),s("label",x,[e.value?(t(),s("span",y,d(e.value),1)):(t(),s("span",v,[m(a.$slots,"default")]))]))}},M={__name:"TextInput",props:{modelValue:{type:String,required:!0},modelModifiers:{}},emits:["update:modelValue"],setup(e,{expose:a}){const r=p(e,"modelValue"),o=_(null);return f(()=>{o.value.hasAttribute("autofocus")&&o.value.focus()}),a({focus:()=>o.value.focus()}),(b,n)=>u((t(),s("input",{class:"rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600","onUpdate:modelValue":n[0]||(n[0]=l=>r.value=l),ref_key:"input",ref:o},null,512)),[[g,r.value]])}};export{V as _,M as a,S as b};
