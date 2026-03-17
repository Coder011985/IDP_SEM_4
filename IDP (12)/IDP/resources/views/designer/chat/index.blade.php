@extends('layouts.designer')

@section('title','Project Chat')

@section('content')

<div class="max-w-5xl mx-auto">

<div class="bg-white shadow-xl rounded-xl overflow-hidden flex flex-col h-[600px]">

<div class="p-4 border-b flex items-center justify-between">

<div>

<div class="font-semibold text-lg">
Project Chat — {{ $project->title }}
</div>

@if($project->client)

<div class="text-sm text-gray-500 flex items-center gap-2 mt-1">

<span id="clientStatusDot" class="w-2 h-2 rounded-full bg-gray-400"></span>

<span id="clientStatusText">Offline</span>

</div>

@endif

</div>

</div>


<div id="chatBox" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">

@php
$lastDate = null;
@endphp

@foreach($messages as $msg)

@php
$currentDate = $msg->created_at->format('Y-m-d');
@endphp

@if($lastDate !== $currentDate)

<div class="flex justify-center my-4">
<span class="bg-gray-300 text-gray-700 shadow-sm text-xs px-3 py-1 rounded-full">
{{ $msg->created_at->isToday() ? 'Today' : ($msg->created_at->isYesterday() ? 'Yesterday' : $msg->created_at->format('M d')) }}
</span>
</div>

@php
$lastDate = $currentDate;
@endphp

@endif

@php
$isMe = $msg->sender_id == auth()->id();
@endphp

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}" data-message="{{ $msg->id }}" data-date="{{ $msg->created_at->format('Y-m-d') }}">

<div class="max-w-sm">

<div class="px-4 py-2 rounded-xl shadow
{{ $isMe ? 'bg-blue-500 text-white' : 'bg-white text-gray-800 border' }}">

@if($msg->message)
<p class="mb-2 text-sm">{{ $msg->message }}</p>
@endif

@if($msg->file_path)

@php
$extension = strtolower(pathinfo($msg->file_path, PATHINFO_EXTENSION));
@endphp

@if(in_array($extension,['jpg','jpeg','png','gif','webp']))

<img src="{{ asset('storage/'.$msg->file_path) }}" class="rounded-lg border max-w-xs">

@else

<a href="{{ asset('storage/'.$msg->file_path) }}" target="_blank" class="underline text-sm">
Download File
</a>

@endif

@endif

</div>

<div class="text-xs text-gray-400 mt-1 flex items-center gap-1 {{ $isMe ? 'justify-end' : '' }}" data-status="{{ $msg->id }}">

<span>{{ $msg->created_at->format('H:i') }}</span>

@if($isMe)

@if($msg->seen_at)
<span class="text-blue-500">✓✓</span>
@elseif($msg->delivered_at)
<span>✓✓</span>
@else
<span>✓</span>
@endif

@endif

</div>

<div class="flex gap-2 mt-1 text-sm reaction-bar" data-chat="{{ $msg->id }}">
<button onclick="react({{ $msg->id }},'👍')">👍</button>
<button onclick="react({{ $msg->id }},'❤️')">❤️</button>
<button onclick="react({{ $msg->id }},'🔥')">🔥</button>
<button onclick="react({{ $msg->id }},'😂')">😂</button>
</div>

</div>

</div>

@endforeach

</div>


<div id="typingIndicator" class="text-sm text-gray-500 px-6 pb-2 hidden">
Someone is typing...
</div>


<div class="border-t p-4 bg-white relative">

<form method="POST"
action="{{ route('designer.projects.chat.send',$project) }}"
onsubmit="return messageInput.value.trim() !== '' || document.getElementById('fileInput').files.length > 0"
enctype="multipart/form-data"
class="flex gap-3 items-center">
@csrf

<button type="button" id="emojiBtn" class="text-xl">😊</button>

<input
type="text"
name="message"
id="messageInput"
autocomplete="off"
placeholder="Type message..."
class="flex-1 border rounded-lg px-4 py-2 text-sm">

<button type="button" id="attachBtn" class="text-xl">+</button>

<input
type="file"
name="file"
id="fileInput"
accept="image/*,.pdf,.doc,.docx,.zip"
class="hidden">

<button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">
Send
</button>

</form>
<div id="attachMenu"
class="hidden absolute bottom-16 left-16 bg-white shadow-lg rounded-lg p-3 text-sm space-y-2">

<button onclick="openFile()" class="block w-full text-left hover:bg-gray-100 px-2 py-1 rounded">📄 Document</button>
<button onclick="openFile()" class="block w-full text-left hover:bg-gray-100 px-2 py-1 rounded">🖼 Photos & videos</button>
<button onclick="openFile()" class="block w-full text-left hover:bg-gray-100 px-2 py-1 rounded">🎤 Audio</button>

</div>
</div>

</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@latest/dist/index.min.js"></script>

<script>

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-TOKEN'] =
document.querySelector('meta[name="csrf-token"]').getAttribute('content')

window.projectId = {{ $project->id }}
window.userId = {{ auth()->id() }}

const chatBox = document.getElementById("chatBox")
const typingIndicator = document.getElementById("typingIndicator")

const statusDot = document.getElementById("clientStatusDot")
const statusText = document.getElementById("clientStatusText")
const hasStatus = statusDot && statusText

const messageInput = document.getElementById("messageInput")
const renderedMessages = new Set()

document.querySelectorAll("[data-message]").forEach(el=>{
renderedMessages.add(parseInt(el.dataset.message))
})
let lastRenderedDate =
    document.querySelector('#chatBox [data-message]:last-child')
    ?.dataset?.date || null

function scrollToBottom(){
chatBox.scrollTop = chatBox.scrollHeight
}

requestAnimationFrame(()=>scrollToBottom())

/*
|--------------------------------------------------------------------------
| Load Older Messages (Scroll Up)
|--------------------------------------------------------------------------
*/

let loadingMessages = false
let noMoreMessages = false

chatBox.addEventListener("scroll", async () => {

if(chatBox.scrollTop > 50) return
if(loadingMessages || noMoreMessages) return

let firstMessage = chatBox.querySelector("[data-message]")
if(!firstMessage) return

let firstId = firstMessage.dataset.message

loadingMessages = true

let oldHeight = chatBox.scrollHeight

try{

let res = await axios.get(`/designer/projects/${window.projectId}/messages?last_id=${firstId}`)

let messages = res.data

if(messages.length === 0){
noMoreMessages = true
loadingMessages = false
return
}

messages.forEach(msg => {

let isMe = msg.sender_id == window.userId

let html = `
<div class="flex ${isMe ? 'justify-end':'justify-start'}" data-message="${msg.id}" data-date="${new Date(msg.created_at).toISOString().split('T')[0]}">
<div class="max-w-sm">

<div class="px-4 py-2 rounded-xl shadow ${isMe ? 'bg-blue-500 text-white':'bg-white border'}">
${msg.message ? `<p class="mb-2 text-sm">${msg.message}</p>`:''}
</div>

<div class="text-xs text-gray-400 mt-1" data-status="${msg.id}">
${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}
</div>

</div>
</div>
`

chatBox.insertAdjacentHTML("afterbegin",html)

})

let newHeight = chatBox.scrollHeight
chatBox.scrollTop = newHeight - oldHeight

}catch(e){
console.error(e)
}

loadingMessages = false

})

/*
|--------------------------------------------------------------------------
| Emoji Picker
|--------------------------------------------------------------------------
*/

const picker = new EmojiButton()

picker.on('emoji', emoji => {
messageInput.value += emoji
})

document.getElementById("emojiBtn").addEventListener("click", () => {
picker.togglePicker(document.getElementById("emojiBtn"))
})


/*
|--------------------------------------------------------------------------
| Echo Presence
|--------------------------------------------------------------------------
*/

if(window.Echo){

window.Echo.join(`project.${window.projectId}`)

// mark messages as seen when chat opens
if(window.projectId){
axios.post(`/projects/${window.projectId}/seen`).catch(()=>{})
}


.here((users)=>{

if(hasStatus && users.length > 1){

statusDot.classList.remove("bg-gray-400")
statusDot.classList.add("bg-green-500")

statusText.innerText = "Online"

}

})

.joining(()=>{
if(!hasStatus) return

statusDot.classList.remove("bg-gray-400")
statusDot.classList.add("bg-green-500")

statusText.innerText = "Online"

})

.leaving(()=>{
if(!hasStatus) return

statusDot.classList.remove("bg-green-500")
statusDot.classList.add("bg-gray-400")

statusText.innerText = "Offline"

})


.listen('.chat.message',(e)=>{

if(renderedMessages.has(e.id)){
return
}

renderedMessages.add(e.id)

if(e.sender_id == window.userId){
return
}

let fileHtml = ''

if(e.file){

let extension = e.file.split('.').pop().toLowerCase()

if(['jpg','jpeg','png','gif','webp'].includes(extension)){
fileHtml = `<img src="/storage/${e.file}" class="rounded-lg border max-w-xs">`
}else{
fileHtml = `<a href="/storage/${e.file}" target="_blank" class="underline text-sm">Download File</a>`
}

}

let msgDate = e.created_at.slice(0,10)

let dateHtml = ''

if(lastRenderedDate !== msgDate){

    let d = new Date(e.created_at)
let today = new Date()

let label = d.toDateString() === today.toDateString()
    ? "Today"
    : d.toDateString() === new Date(today - 86400000).toDateString()
    ? "Yesterday"
    : d.toLocaleDateString(undefined,{month:"short",day:"numeric"})

    dateHtml = `
    <div class="flex justify-center my-4">
        <span class="bg-gray-300 text-gray-700 shadow-sm text-xs px-3 py-1 rounded-full">
            ${label}
        </span>
    </div>
    `

    lastRenderedDate = msgDate
}
let html = `
<div class="flex justify-start" data-message="${e.id}" data-date="${msgDate}">
<div class="max-w-sm">

<div class="px-4 py-2 rounded-xl shadow bg-white border">
${e.message ? `<p class="mb-2 text-sm">${e.message}</p>` : ''}
${fileHtml}
</div>

<div class="text-xs text-gray-400 mt-1" data-status="${e.id}">
${new Date(e.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}
</div>

<div class="flex gap-2 mt-1 text-sm reaction-bar" data-chat="${e.id}">
<button onclick="react(${e.id},'👍')">👍</button>
<button onclick="react(${e.id},'❤️')">❤️</button>
<button onclick="react(${e.id},'🔥')">🔥</button>
<button onclick="react(${e.id},'😂')">😂</button>
</div>

</div>
</div>
`

chatBox.insertAdjacentHTML('beforeend',dateHtml + html)

if(chatBox.children.length > 300){
    chatBox.removeChild(chatBox.firstElementChild)
}

if(chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 120){
    requestAnimationFrame(()=>scrollToBottom())
}

})


.listen('.chat.typing',(e)=>{

if(e.user_id !== window.userId){

typingIndicator.innerText = e.user_name + " is typing..."

typingIndicator.classList.remove("hidden")

clearTimeout(window.typingHideTimer)

window.typingHideTimer = setTimeout(()=>{
typingIndicator.classList.add("hidden")
},1500)

}

})


.listen('.chat.reaction.updated',(e)=>{

let bar = document.querySelector(`[data-chat="${e.chat_id}"]`)

if(!bar) return

let counts = '<span class="ml-2 text-xs text-gray-500">'

Object.keys(e.reactions).forEach(r=>{
counts += `<span class="px-1 inline-flex items-center">${r} ${e.reactions[r]}</span>`
})

counts += '</span>'

bar.innerHTML = `
<button onclick="react(${e.chat_id},'👍')">👍</button>
<button onclick="react(${e.chat_id},'❤️')">❤️</button>
<button onclick="react(${e.chat_id},'🔥')">🔥</button>
<button onclick="react(${e.chat_id},'😂')">😂</button>
${counts}
`

})

.listen('.chat.seen',(e)=>{

e.message_ids.forEach(id=>{

let status = document.querySelector(`[data-status="${id}"]`)

if(status){
status.innerHTML = `<span class="text-blue-500">✓✓</span>`
}

})

})

}


/*
|--------------------------------------------------------------------------
| Typing Event
|--------------------------------------------------------------------------
*/

let typingTimer
let isTyping = false

if(messageInput){

messageInput.addEventListener("input",()=>{

if(!isTyping){
axios.post(`/designer/projects/${window.projectId}/typing`)
isTyping = true
}

clearTimeout(typingTimer)

typingTimer = setTimeout(()=>{
isTyping = false
},2000)

})

}

const attachBtn = document.getElementById("attachBtn")
const attachMenu = document.getElementById("attachMenu")
const fileInput = document.getElementById("fileInput")

if(attachBtn){

attachBtn.addEventListener("click", () => {
attachMenu.classList.toggle("hidden")
})

}

function openFile(){
if(fileInput){
fileInput.click()
}
if(attachMenu){
attachMenu.classList.add("hidden")
}
}

document.addEventListener("click",(e)=>{

if(attachBtn && attachMenu && !attachBtn.contains(e.target) && !attachMenu.contains(e.target)){
attachMenu.classList.add("hidden")
}

})


// reaction system
function react(chatId,reaction){

axios.post(`/chat/${chatId}/react`,{
reaction:reaction
}).catch(()=>{})

}

</script>

@endsection
