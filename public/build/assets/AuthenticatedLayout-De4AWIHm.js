import{A as B}from"./ApplicationLogo-BF2iB-KF.js";import{p as M,aY as D,h as v,k as S,o as l,f as p,b as e,r as g,i as w,bH as $,a as n,w as o,n as u,M as N,c as k,u as y,j as m,d,t as b,g as j}from"./app-DQx6G7PF.js";const E={class:"relative"},z={__name:"Dropdown",props:{align:{type:String,default:"right"},width:{type:String,default:"48"},contentClasses:{type:String,default:"py-1 bg-white dark:bg-gray-700"}},setup(a){const s=a,t=h=>{i.value&&h.key==="Escape"&&(i.value=!1)};M(()=>document.addEventListener("keydown",t)),D(()=>document.removeEventListener("keydown",t));const r=v(()=>({48:"w-48"})[s.width.toString()]),f=v(()=>s.align==="left"?"ltr:origin-top-left rtl:origin-top-right start-0":s.align==="right"?"ltr:origin-top-right rtl:origin-top-left end-0":"origin-top"),i=S(!1);return(h,c)=>(l(),p("div",E,[e("div",{onClick:c[0]||(c[0]=_=>i.value=!i.value)},[g(h.$slots,"trigger")]),w(e("div",{class:"fixed inset-0 z-40",onClick:c[1]||(c[1]=_=>i.value=!1)},null,512),[[$,i.value]]),n(N,{"enter-active-class":"transition ease-out duration-200","enter-from-class":"opacity-0 scale-95","enter-to-class":"opacity-100 scale-100","leave-active-class":"transition ease-in duration-75","leave-from-class":"opacity-100 scale-100","leave-to-class":"opacity-0 scale-95"},{default:o(()=>[w(e("div",{class:u(["absolute z-50 mt-2 rounded-md shadow-lg",[r.value,f.value]]),style:{display:"none"},onClick:c[2]||(c[2]=_=>i.value=!1)},[e("div",{class:u(["rounded-md ring-1 ring-black ring-opacity-5",a.contentClasses])},[g(h.$slots,"content")],2)],2),[[$,i.value]])]),_:3})]))}},C={__name:"DropdownLink",props:{href:{type:String,required:!0}},setup(a){return(s,t)=>(l(),k(y(m),{href:a.href,class:"block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:bg-gray-800"},{default:o(()=>[g(s.$slots,"default")]),_:3},8,["href"]))}},L={__name:"NavLink",props:{href:{type:String,required:!0},active:{type:Boolean}},setup(a){const s=a,t=v(()=>s.active?"inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out":"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out");return(r,f)=>(l(),k(y(m),{href:a.href,class:u(t.value)},{default:o(()=>[g(r.$slots,"default")]),_:3},8,["href","class"]))}},x={__name:"ResponsiveNavLink",props:{href:{type:String,required:!0},active:{type:Boolean}},setup(a){const s=a,t=v(()=>s.active?"block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-600 text-start text-base font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 focus:outline-none focus:text-indigo-800 dark:focus:text-indigo-200 focus:bg-indigo-100 dark:focus:bg-indigo-900 focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out":"block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out");return(r,f)=>(l(),k(y(m),{href:a.href,class:u(t.value)},{default:o(()=>[g(r.$slots,"default")]),_:3},8,["href","class"]))}},O={class:""},V={class:"min-h-screen bg-gray-100 dark:bg-gray-900"},q={class:"border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"},A={class:"mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"},P={class:"flex h-16 justify-between"},T={class:"flex"},H={class:"flex shrink-0 items-center"},R={class:"hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"},U={class:"hidden sm:ms-6 sm:flex sm:items-center"},Y={class:"relative ms-3"},F={class:"inline-flex rounded-md"},G={type:"button",class:"inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"},I={class:"-me-2 flex items-center sm:hidden"},J={class:"h-6 w-6",stroke:"currentColor",fill:"none",viewBox:"0 0 24 24"},K={class:"space-y-1 pb-3 pt-2"},Q={class:"border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"},W={class:"px-4"},X={class:"text-base font-medium text-gray-800 dark:text-gray-200"},Z={class:"text-sm font-medium text-gray-500"},ee={class:"mt-3 space-y-1"},te={key:0,class:"bg-white shadow dark:bg-gray-800"},re={class:"mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-4"},se={class:"w-full max-w-screen-xl mx-auto p-0"},ne={__name:"AuthenticatedLayout",setup(a){const s=S(!1);return(t,r)=>(l(),p("div",O,[e("div",V,[e("nav",q,[e("div",A,[e("div",P,[e("div",T,[e("div",H,[n(y(m),{href:t.route("dashboard")},{default:o(()=>[n(B,{class:"block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"})]),_:1},8,["href"])]),e("div",R,[n(L,{href:t.route("dashboard"),active:t.route().current("dashboard")},{default:o(()=>r[1]||(r[1]=[d(" Dashboard ")])),_:1},8,["href","active"]),n(L,{href:t.route("dashboard.order")},{default:o(()=>r[2]||(r[2]=[d(" Order ")])),_:1},8,["href"])])]),e("div",U,[e("div",Y,[n(z,{align:"right",width:"48"},{trigger:o(()=>[e("span",F,[e("button",G,[d(b(t.$page.props.auth.user.name)+" ",1),r[3]||(r[3]=e("svg",{class:"-me-0.5 ms-2 h-4 w-4",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[e("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})],-1))])])]),content:o(()=>[n(C,{href:t.route("profile.edit")},{default:o(()=>r[4]||(r[4]=[d(" Profile ")])),_:1},8,["href"]),n(C,{href:t.route("logout"),method:"post",as:"button"},{default:o(()=>r[5]||(r[5]=[d(" Log Out ")])),_:1},8,["href"])]),_:1})])]),e("div",I,[e("button",{onClick:r[0]||(r[0]=f=>s.value=!s.value),class:"inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"},[(l(),p("svg",J,[e("path",{class:u({hidden:s.value,"inline-flex":!s.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 6h16M4 12h16M4 18h16"},null,2),e("path",{class:u({hidden:!s.value,"inline-flex":s.value}),"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"},null,2)]))])])])]),e("div",{class:u([{block:s.value,hidden:!s.value},"sm:hidden"])},[e("div",K,[n(x,{href:t.route("dashboard"),active:t.route().current("dashboard")},{default:o(()=>r[6]||(r[6]=[d(" Dashboard ")])),_:1},8,["href","active"])]),e("div",Q,[e("div",W,[e("div",X,b(t.$page.props.auth.user.name),1),e("div",Z,b(t.$page.props.auth.user.email),1)]),e("div",ee,[n(x,{href:t.route("profile.edit")},{default:o(()=>r[7]||(r[7]=[d(" Profile ")])),_:1},8,["href"]),n(x,{href:t.route("logout"),method:"post",as:"button"},{default:o(()=>r[8]||(r[8]=[d(" Log Out ")])),_:1},8,["href"])])])],2)]),t.$slots.header?(l(),p("header",te,[e("div",re,[g(t.$slots,"header")])])):j("",!0),e("main",se,[g(t.$slots,"default")])])]))}};export{ne as _};
