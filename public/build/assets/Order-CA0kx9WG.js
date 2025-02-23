import{k as l,h as W,p as le,q as re,s as ne,l as ie,f as v,a as w,u as de,w as ue,F as z,x as ce,o as g,Z as pe,b as t,i as Y,y as j,m as B,g as me,n as ve,t as r,d as i}from"./app-DoGou6e8.js";import{_ as ge}from"./AuthenticatedLayout-C05kFhRR.js";import"./ApplicationLogo-hE68ScMq.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const ye={class:"max-w-4xl mx-auto p-3 flex flex-col md:flex-row md:justify-between md:items-center gap-2"},be={class:"flex flex-col w-full md:flex-row md:items-center gap-2"},fe={class:"flex w-full gap-2"},xe=["value"],he=["value"],we={class:"max-w-3xl mx-auto p-3 bg-white rounded-lg shadow-md mt-2"},_e={class:"w-full border-collapse border border-gray-300 text-sm"},ke={class:"bg-gray-100 text-gray-700"},Se={class:"p-3 border border-gray-300"},Me={class:"p-3 border border-gray-300"},De={class:"text-center"},Ce={class:"p-3 border border-gray-300 font-semibold"},Ae={class:"p-3 border border-gray-300"},Oe={class:"p-3 border border-gray-300 font-semibold"},We={class:"text-xs text-gray-500"},ze={class:"p-3 border border-gray-300"},Fe={class:"text-xs text-gray-500"},Le={class:"p-3 border border-gray-300 font-semibold"},Ne={class:"text-xs text-gray-500"},$e={class:"p-3 border border-gray-300"},Te={class:"text-xs text-gray-500"},Ve={class:"p-3 border border-gray-300 font-semibold"},Ye={class:"p-3 border border-gray-300"},je={class:"flex justify-center max-w-3xl mx-auto my-2"},Be={class:"w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md"},Pe={class:"flex justify-center max-w-3xl mx-auto my-2"},Ue={class:"w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md"},Ee={class:"flex justify-center max-w-3xl mx-auto my-2"},Ie={class:"w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md"},Je={class:"flex justify-center max-w-3xl mx-auto my-2"},He={class:"w-full max-w-[800px] p-4 bg-white rounded-lg shadow-md"},qe={class:"flex items-center justify-between mb-4"},Xe={class:"flex items-center gap-2"},Ze=["value"],tt={__name:"Order",setup(Ge){const P=l([]),F=l([]),L=l({}),y=l({}),b=l({}),_=l({}),D=l({}),U=new Date,C=U.getFullYear(),R=String(U.getMonth()+1).padStart(2,"0"),E=W(()=>Array.from({length:12},(a,e)=>{const s=String(e+1).padStart(2,"0");return{value:s,label:new Date(`${C}-${s}-01`).toLocaleString("default",{month:"long"})}})),I=W(()=>{const a=C-5,e=C;return Array.from({length:e-a+1},(s,o)=>a+o)}),u=l(R),c=l(C),J=a=>L.value[a]?new Date(L.value[a]).toLocaleString("default",{month:"long",year:"numeric"}):"",f=W(()=>J("selected_date")),x=W(()=>J("comparison_date")),ee=l({chart:{id:"line-chart",events:{markerClick:(a,e,{dataPointIndex:s})=>{e.toggleDataPointSelection(s)}}},xaxis:{type:"datetime"},stroke:{width:[2,1],dashArray:[0,5]},dataLabels:{enabled:!0,formatter:(a,{seriesIndex:e,dataPointIndex:s,w:o})=>{if(e!==0)return"";const p=o.globals.series[0][s],d=s>0?o.globals.series[0][s-1]:null;return d!==null&&Math.abs(p-d)>500&&p!==0?a:""},style:{fontSize:"12px",colors:["#000"]},offsetY:-10},plotOptions:{line:{zIndex:1}},tooltip:{shared:!0,x:{format:"ddd dd, MMM"},y:{formatter:a=>a.toLocaleString()},custom:({series:a,seriesIndex:e,dataPointIndex:s,w:o})=>{var h,M;const p=new Date(o.globals.seriesX[0][s]).toLocaleDateString("en-US",{weekday:"short",day:"2-digit",month:"short"}),d=((h=F.value[s])==null?void 0:h[2])??"N/A",n=((M=a[1])==null?void 0:M[s])??0;return`<div style="padding: 10px;">
                <strong>${p}</strong><br/>
                <span style="color:#FF5733">● ${f.value}: ${a[0][s]}</span><br/>
                <span style="color:#3773f5">● ${x.value} (${d}): ${n}</span>
              </div>`}},markers:{size:3,strokeWidth:1,hover:{sizeOffset:2}}}),H=l([]),N=l({title:{text:""},chart:{id:"day-of-week-analytics-bar-chart",type:"bar",stacked:!1,background:"#FFFFFF"},plotOptions:{bar:{horizontal:!1,columnWidth:"45%",endingShape:"rounded",dataLabels:{position:"top"}}},xaxis:{categories:["Sun","Mon","Tue","Wed","Thur","Fri","Sat"],title:{text:"Order by Weekdays",style:{fontSize:"14px",fontWeight:"bold"}}},yaxis:{title:{text:"Orders"}},fill:{opacity:[1,.2]},legend:{position:"bottom",horizontalAlign:"center"},dataLabels:{enabled:!0,style:{fontSize:"12px",colors:["#000"]},position:"top",floating:!0,offsetY:-20}}),k=l([[],[]]);l({chart:{id:"day-of-week-analytics-bar-chart",type:"bar",stacked:!1},plotOptions:{bar:{horizontal:!1,columnWidth:"45%",endingShape:"rounded",dataLabels:{position:"top"}}},xaxis:{categories:["Day 1-10","Day 11-20","Day 21-30(31)"]},yaxis:{title:{text:"Orders"},labels:{formatter:function(a){return Math.floor(a)}}},fill:{opacity:[1,.1]},legend:{position:"bottom",horizontalAlign:"center"},dataLabels:{enabled:!0,style:{fontSize:"12px",colors:["#000"]},position:"top",floating:!0,offsetY:-20,formatter:function(a){return Math.floor(a)}}});const A=l(6),te=l([3,4,6]);l([[]]);const q=l({}),X=l([]),ae=a=>{var n,h,M,Q;const e=((h=(n=D.value)==null?void 0:n[a])==null?void 0:h.selected)||{},s=((Q=(M=D.value)==null?void 0:M[a])==null?void 0:Q.comparison)||{},o=Object.keys(e.average||{}),p=o.map(V=>e.average[V]||0),d=o.map(V=>s.average[V]||0);return{series:[{name:f.value,data:p},{name:x.value,data:d}],categories:o}},Z=()=>{const a=ae(A.value);X.value=a.series,q.value={chart:{type:"bar",toolbar:{show:!1}},plotOptions:{bar:{horizontal:!1,columnWidth:"45%",endingShape:"rounded",dataLabels:{position:"top"}}},xaxis:{categories:a.categories,title:{text:A.value+" Periods",style:{fontSize:"14px",fontWeight:"bold"}}},yaxis:{labels:{formatter:e=>e.toFixed(2)}},tooltip:{y:{formatter:e=>e.toFixed(2)}},dataLabels:{enabled:!0,style:{fontSize:"12px",colors:["#000"]},position:"top",floating:!0,offsetY:-20}}},S=async()=>{await ce.get("/api/dashboard/order/month",{params:{month:u.value,year:c.value}}).then(a=>{if(a.data.success===!0){P.value=a.data.packing_data.selected_data,F.value=a.data.packing_data.comparison_data,L.value=a.data.analytics_data.date_comparison_between,y.value=a.data.analytics_data.selected_analytics,b.value=a.data.analytics_data.comparison_analytics,_.value=a.data.analytics_data.day_of_week_analytics,D.value=a.data.analytics_data.period_analytics,H.value=[{name:f.value,data:P.value},{name:x.value,data:F.value}],k.value=[];const e=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],s=e.map(n=>_.value.selected.average[n]||0),o=e.map(n=>_.value.comparison.average[n]||0);console.log(N.value),k.value.push([{name:f.value,data:s},{name:x.value,data:o}]);const p=e.map(n=>_.value.selected.max[n]||0),d=e.map(n=>_.value.comparison.max[n]||0);k.value.push([{name:f.value,data:p},{name:x.value,data:d}]),D.value=a.data.analytics_data.period_analytics,Z()}})},m=a=>a?a.toLocaleString():"0",O=a=>{if(!a||a==="N/A")return"N/A";const e=a.split(" ");if(e.length<3)return"N/A";const s=parseInt(e[1],10),o=e[2].replace(",",""),d={Jan:"ม.ค.",Feb:"ก.พ.",Mar:"มี.ค.",Apr:"เม.ย.",May:"พ.ค.",Jun:"มิ.ย.",Jul:"ก.ค.",Aug:"ส.ค.",Sep:"ก.ย.",Oct:"ต.ค.",Nov:"พ.ย.",Dec:"ธ.ค."}[o]||o;return`${{Sun:"อาทิตย์",Mon:"จันทร์",Tue:"อังคาร",Wed:"พุธ",Thu:"พฤหัส",Fri:"ศุกร์",Sat:"เสาร์"}[e[0]]||e[0]} ${s} ${d}`},$=l(!1),G=()=>{$.value=window.scrollY>64};le(()=>{S(),window.addEventListener("scroll",G)}),re(()=>{window.removeEventListener("scroll",G)});const oe=()=>{let a=parseInt(u.value),e=parseInt(c.value);a===1?(a=12,e-=1):a-=1,u.value=String(a).padStart(2,"0"),c.value=String(e),S()},T=l(!1),K=()=>{let a=parseInt(u.value),e=parseInt(c.value);a===12?(a=1,e+=1):a+=1,T.value=I.value.includes(e)&&E.value.some(s=>s.value===String(a).padStart(2,"0"))},se=()=>{if(!T.value)return;let a=parseInt(u.value),e=parseInt(c.value);a===12?(a=1,e+=1):a+=1,u.value=String(a).padStart(2,"0"),c.value=String(e),K(),S()};return ne([u,c],()=>{K()}),(a,e)=>{const s=ie("apexchart");return g(),v(z,null,[w(de(pe),{title:"Order"}),w(ge,null,{default:ue(()=>[t("div",{class:ve({"fixed top-0 left-0 w-full z-20 bg-white shadow-md border-b border-gray-200 transition-transform duration-300 transform":!0,"-translate-y-full":!$.value,"translate-y-0":$.value})},[t("div",ye,[t("div",be,[e[5]||(e[5]=t("label",{class:"block text-sm font-medium text-gray-700"},"ข้อมูลเดือน:",-1)),t("div",fe,[t("div",{class:"flex items-center gap-2 mt-2 md:mt-0"},[t("button",{onClick:oe,class:"text-gray-500 hover:text-gray-700 transition rounded-full p-2 hover:bg-gray-100 focus:outline-none",title:"Previous Month"},e[3]||(e[3]=[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M15 19l-7-7 7-7"})],-1)]))]),Y(t("select",{"onUpdate:modelValue":e[0]||(e[0]=o=>u.value=o),class:"w-1/2 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2",onChange:S},[(g(!0),v(z,null,B(E.value,o=>(g(),v("option",{key:o.value,value:o.value},r(o.label),9,xe))),128))],544),[[j,u.value]]),Y(t("select",{"onUpdate:modelValue":e[1]||(e[1]=o=>c.value=o),class:"w-1/2 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2",onChange:S},[(g(!0),v(z,null,B(I.value,o=>(g(),v("option",{key:o,value:o},r(o),9,he))),128))],544),[[j,c.value]]),T.value?(g(),v("button",{key:0,onClick:se,class:"text-gray-500 hover:text-gray-700 transition rounded-full p-2 hover:bg-gray-100 focus:outline-none",title:"Next Month"},e[4]||(e[4]=[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M9 5l7 7-7 7"})],-1)]))):me("",!0)])])])],2),t("div",we,[e[19]||(e[19]=t("h2",{class:"text-xl font-bold text-gray-700 mb-2"},"สถิติต่างๆ",-1)),t("table",_e,[t("thead",ke,[t("tr",null,[e[6]||(e[6]=t("th",{class:"p-3 border border-gray-300"},"Metric",-1)),t("th",Se,r(f.value),1),t("th",Me,r(x.value),1)])]),t("tbody",De,[t("tr",null,[e[7]||(e[7]=t("td",{class:"p-3 border border-gray-300"},"รวมทั้งเดือน",-1)),t("td",Ce,r(m(y.value.sum)),1),t("td",Ae,r(m(b.value.sum)),1)]),t("tr",null,[e[12]||(e[12]=t("td",{class:"p-3 border border-gray-300"},"วันที่สูงสุด",-1)),t("td",Oe,[i(r(m(y.value.max_value))+" ",1),e[8]||(e[8]=t("br",null,null,-1)),e[9]||(e[9]=i()),t("span",We,r(O(y.value.max_date)),1)]),t("td",ze,[i(r(m(b.value.max_value))+" ",1),e[10]||(e[10]=t("br",null,null,-1)),e[11]||(e[11]=i()),t("span",Fe,r(O(b.value.max_date)),1)])]),t("tr",null,[e[17]||(e[17]=t("td",{class:"p-3 border border-gray-300"},"วันที่ต่ำสุด",-1)),t("td",Le,[i(r(m(y.value.min_value))+" ",1),e[13]||(e[13]=t("br",null,null,-1)),e[14]||(e[14]=i()),t("span",Ne,r(O(y.value.min_date)),1)]),t("td",$e,[i(r(m(b.value.min_value))+" ",1),e[15]||(e[15]=t("br",null,null,-1)),e[16]||(e[16]=i()),t("span",Te,r(O(b.value.min_date)),1)])]),t("tr",null,[e[18]||(e[18]=t("td",{class:"p-3 border border-gray-300"},"เฉลี่ย",-1)),t("td",Ve,r(m(y.value.average)),1),t("td",Ye,r(m(b.value.average)),1)])])])]),t("div",je,[t("div",Be,[e[20]||(e[20]=i(" Order by Day in Month ")),w(s,{class:"w-full h-[250px] sm:h-[400px]",height:"250px",type:"line",options:ee.value,series:H.value},null,8,["options","series"])])]),t("div",Pe,[t("div",Ue,[e[21]||(e[21]=i(" Average Order by Day of Week ")),w(s,{class:"w-full h-[350px]",type:"bar",options:N.value,series:k.value[0]},null,8,["options","series"])])]),t("div",Ee,[t("div",Ie,[e[22]||(e[22]=i(" Max Order by Day of Week ")),w(s,{class:"w-full h-[350px]",type:"bar",options:N.value,series:k.value[1]},null,8,["options","series"])])]),t("div",Je,[t("div",He,[t("div",qe,[e[24]||(e[24]=t("h3",{class:"text-lg font-semibold text-gray-700"},"Average Order by Period",-1)),t("div",Xe,[e[23]||(e[23]=t("label",{for:"period-select",class:"text-sm text-gray-600"},"Select Periods:",-1)),Y(t("select",{id:"period-select","onUpdate:modelValue":e[2]||(e[2]=o=>A.value=o),onChange:Z,class:"rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 pr-10"},[(g(!0),v(z,null,B(te.value,o=>(g(),v("option",{key:o,value:o},r(o),9,Ze))),128))],544),[[j,A.value]])])]),w(s,{class:"w-full h-[350px]",type:"bar",options:q.value,series:X.value},null,8,["options","series"])])])]),_:1})],64)}}};export{tt as default};
