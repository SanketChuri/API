function makeRequest(){var t=document.getElementById("apiRequestForm"),e=t.requestType.value,n="https://student.csc.liv.ac.uk/~sgschuri/v2"+t.apiPath.value,t=t.data.value,o={method:e,headers:{"Content-Type":"application/json"}};if("GET"!==e&&t)try{JSON.parse(t),o.body=t}catch(t){return void alert("Invalid Json format")}fetch(n,o).then(t=>(document.getElementById("statusCode").textContent=t.status,t.text())).then(e=>{try{var t=JSON.parse(e);document.getElementById("responseBody").textContent=JSON.stringify(t,null,2)}catch(t){document.getElementById("responseBody").textContent=e}}).catch(t=>{document.getElementById("statusCode").textContent="Error",document.getElementById("responseBody").textContent="Error: "+t.message})}function resetOutput(){document.getElementById("statusCode").textContent="",document.getElementById("responseBody").textContent=""}
