@extends('layouts.client')

@section('title','Project Chat')

@section('content')

<div class="max-w-5xl mx-auto">

<div class="bg-white shadow-xl rounded-xl overflow-hidden flex flex-col h-[600px]">

<div class="p-4 border-b flex items-center justify-between">

<div>

<h2 class="text-lg font-semibold">
Project Chat — {{ $project->title }}
</h2>

@if($project->designer)

<div class="text-sm text-gray-500 flex items-center gap-2 mt-1">

<span id="designerStatusDot" class="w-2 h-2 rounded-full bg-gray-400"></span>

<span id="designerStatusText">Offline</span>

</div>

@endif

</div>

</div>


<div id="chatBox" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 scroll-smooth">

@foreach($messages as $msg)

@php
$isMe = $msg->sender_id == auth()->id();
@endphp

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}" data-message="{{ $msg->id }}">

<div class="max-w-sm">

<div class="px-4 py-2 rounded-xl shadow
{{ $isMe ? 'bg-emerald-600 text-white' : 'bg-white text-gray-800 border' }}">

@if($msg->message)
<p class="mb-2 text-sm">{{ $msg->message }}</p>
@endif

@if($msg->file_path)

@php
$extension = strtolower(pathinfo($msg->file_path, PATHINFO_EXTENSION));
@endphp

@if(in_array($extension,['jpg','jpeg','png','gif','webp']))

<img
src="{{ asset('storage/'.$msg->file_path) }}"
class="rounded-lg border max-w-xs">

@else

<a
href="{{ asset('storage/'.$msg->file_path) }}"
target="_blank"
class="underline text-sm">
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


<div id="typingIndicator"
class="px-6 pb-2 text-sm text-gray-400 hidden">
Someone is typing...
</div>


<div class="border-t p-4 bg-white relative">

<form method="POST"
action="{{ route('client.projects.chat.send',$project) }}"
onsubmit="return messageInput.value.trim() !== '' || document.getElementById('fileInput').files.length > 0"
enctype="multipart/form-data"
class="flex gap-3 items-center">

@csrf

<button type="button" id="emojiBtn"
class="text-xl">😊</button>

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

<button
class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm">
Send
</button>

</form>
<script>
const chatForm = document.querySelector('form[action*="chat.send"]')

if(chatForm){
chatForm.addEventListener("submit",function(){
setTimeout(()=>{
const box = document.getElementById("chatBox")
if(box){
box.scrollTop = box.scrollHeight
}
},200)
})
}
</script>

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
const messageInput = document.getElementById("messageInput")

const statusDot = document.getElementById("designerStatusDot")
const statusText = document.getElementById("designerStatusText")
const hasStatus = statusDot && statusText

/*
|--------------------------------------------------------------------------
| Message Cache (Duplicate Prevention)
|--------------------------------------------------------------------------
*/

const renderedMessages = new Set()

document.querySelectorAll("[data-message]").forEach(el=>{
    renderedMessages.add(parseInt(el.dataset.message))
})

/*
|--------------------------------------------------------------------------
| Sound Notification
|--------------------------------------------------------------------------
*/

let messageSound = null

try{
    messageSound = new Audio('{{ asset('sounds/message.mp3') }}')
    messageSound.volume = 0.6
}catch(e){
    messageSound = null
}

/*
|--------------------------------------------------------------------------
| Scroll
|--------------------------------------------------------------------------
*/

function scrollToBottom(){
    if(!chatBox) return
    chatBox.scrollTop = chatBox.scrollHeight
}

requestAnimationFrame(()=>scrollToBottom())

/*
|--------------------------------------------------------------------------
| Emoji Picker
|--------------------------------------------------------------------------
*/

let picker = null

try{
    picker = new EmojiButton()
}catch(e){
    picker = null
}

if(picker){
picker.on('emoji', emoji => {
    messageInput.value += emoji
})
}

const emojiBtn = document.getElementById("emojiBtn")

if(emojiBtn){
   emojiBtn.addEventListener("click", () => {
    if(picker){
        picker.togglePicker(emojiBtn)
    }
})
}

/*
|--------------------------------------------------------------------------
| Echo Presence
|--------------------------------------------------------------------------
*/

if(typeof window.Echo !== "undefined"){

window.Echo.join(`project.${window.projectId}`)

axios.post(`/projects/${window.projectId}/seen`).catch(()=>{})

.here((users)=>{

if(hasStatus && users.length > 1){
statusDot.classList.remove("bg-gray-400")
statusDot.classList.add("bg-green-500")
statusText.innerText = "Online"
}

})

.joining(()=>{

if(hasStatus){
statusDot.classList.remove("bg-gray-400")
statusDot.classList.add("bg-green-500")
statusText.innerText = "Online"
}

})

.leaving(()=>{

if(hasStatus){
statusDot.classList.remove("bg-green-500")
statusDot.classList.add("bg-gray-400")
statusText.innerText = "Offline"
}

})

.listen('.chat.message',(e)=>{

/*
|--------------------------------------------------------------------------
| Prevent duplicate rendering
|--------------------------------------------------------------------------
*/

if(renderedMessages.has(e.id)){
    return
}

renderedMessages.add(e.id)

/*
|--------------------------------------------------------------------------
| Play sound
|--------------------------------------------------------------------------
*/

if(e.sender_id !== window.userId && messageSound){
    messageSound.currentTime = 0
    messageSound.play().catch(()=>{})
}

/*
|--------------------------------------------------------------------------
| File preview
|--------------------------------------------------------------------------
*/

let fileHtml = ''

if(e.file && e.file.length){

let extension = e.file.split('.').pop().toLowerCase()

if(['jpg','jpeg','png','gif','webp'].includes(extension)){
fileHtml = `<img src="/storage/${e.file}" class="rounded-lg border max-w-xs">`
}else{
fileHtml = `<a href="/storage/${e.file}" target="_blank" class="underline text-sm">Download File</a>`
}

}

/*
|--------------------------------------------------------------------------
| Message HTML
|--------------------------------------------------------------------------
*/

let isMe = e.sender_id == window.userId

let html = `
<div class="flex ${isMe ? 'justify-end':'justify-start'}" data-message="${e.id}">
<div class="max-w-sm">

<div class="px-4 py-2 rounded-xl shadow ${isMe ? 'bg-emerald-600 text-white':'bg-white border'}">

${e.message ? `<p class="mb-2 text-sm">${e.message}</p>` : ''}
${fileHtml}

</div>

<div class="text-xs text-gray-400 mt-1 ${isMe ? 'text-right':''}" data-status="${e.id}">
${e.time ?? 'now'}
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

chatBox.insertAdjacentHTML('beforeend', html)

/*
|--------------------------------------------------------------------------
| Memory protection
|--------------------------------------------------------------------------
*/

if(chatBox.children.length > 300){
chatBox.removeChild(chatBox.firstElementChild)
}

requestAnimationFrame(()=>scrollToBottom())

})

.listen('.chat.typing',(e)=>{

if(e.user_id !== window.userId){

if(typingIndicator){
typingIndicator.innerText = e.user_name + " is typing..."
typingIndicator.classList.remove("hidden")
}
clearTimeout(window.typingTimer)

window.typingTimer = setTimeout(()=>{
if(typingIndicator){
typingIndicator.classList.add("hidden")
}
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
| Typing Sender
|--------------------------------------------------------------------------
*/

let typingTimeout
let typingActive = false

if(messageInput){

messageInput.addEventListener("input",()=>{

if(!typingActive){
axios.post(`/client/projects/${window.projectId}/typing`).catch(()=>{})
typingActive = true
}

clearTimeout(typingTimeout)

typingTimeout = setTimeout(()=>{
typingActive = false
},2000)

})

}

/*
|--------------------------------------------------------------------------
| File Attach
|--------------------------------------------------------------------------
*/

const attachBtn = document.getElementById("attachBtn")
const attachMenu = document.getElementById("attachMenu")
const fileInput = document.getElementById("fileInput")

if(attachBtn && attachMenu){

attachBtn.addEventListener("click", function(e){
e.stopPropagation()
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

if(attachBtn && attachMenu){
if(!attachBtn.contains(e.target) && !attachMenu.contains(e.target)){
attachMenu.classList.add("hidden")
}
}

})

/*
|--------------------------------------------------------------------------
| Reactions
|--------------------------------------------------------------------------
*/

function react(chatId,reaction){

axios.post(`/chat/${chatId}/react`,{
reaction:reaction
}).catch(err=>{
console.log("Reaction error",err)
})

}

</script>

@endsection
