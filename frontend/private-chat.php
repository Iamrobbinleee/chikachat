<!-- Private Chat -->
<div class="ui basic segment">
  <div class="ui comments" style="max-height:400px; max-width: 100%; overflow-y:auto;">
    
    <!-- Left side -->
    <div class="comment">
      <div class="content" style="text-align:left;">
        <i class="user icon"></i> <a class="author" id="selected-user"></a>
        <div class="text" style="background:#f1f1f1; display:inline-block; padding:10px; border-radius:12px;">
          Hi there! How are you?
        </div>
        <div class="metadata">
          <span class="date">Today at 5:42PM</span>
        </div>
      </div>
    </div>

    <!-- Right side -->
    <div class="comment">
      <div class="content" style="text-align:right;">
        <div class="metadata">
          <span class="date">Today at 5:43PM</span>
        </div>
        <div class="text" style="background:#2185d0; color:white; display:inline-block; padding:10px; border-radius:12px;">
          I'm good, thanks! What about you?
        </div>
        <a class="author">You</a> <i class="user icon"></i>
      </div>
    </div>
  </div>

  <!-- Reply -->
  <form class="ui reply form" style="margin-top:10px;">
    <div class="field">
      <textarea style="max-height: 50px;" placeholder="Type a message..."></textarea>
    </div>
    <div style="text-align: right;">
        <button class="ui blue labeled submit icon button">
            <i class="paper plane icon"></i> Send
        </button>
    </div>
  </form>
</div>
