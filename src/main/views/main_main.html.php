<div id="main_main">

<div class="container-fluid">
<div class="row-fluid">

                <div class="x_panel statuspanel">
                  <div class="x_title">
                    <h2>Status <small></small></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">


	<div class="col-xs-8">
		<p v-html="statusMessage"></p>
	</div>
	<div class="col-xs-4">
	<button v-on:click="performAction('disarm');" class="btn-lg statuspanel__btn" :disabled="isDisarmed" v-bind:class="{'btn-default':isArmed}" v-bind:class="{disabled:isDisarmed}"><h4>Disarm</h4></button>
	</div>

	<div class="col-xs-8">
		<p>Activates doors, windows, and internal motion sensors.</p>
	</div>
	<div class="col-xs-4">
	<button v-on:click="performAction('away');" class="btn-lg statuspanel__btn" :disabled="isArmed" v-bind:class="{'btn-primary':isDisarmed}" v-bind:class="{disabled:isarmed}"><h4>Away</h4></button>
	</div>

	<div class="col-xs-8">
		<p>Activates doors and windows.</p>
	</div>
	<div class="col-xs-4">
	<button v-on:click="performAction('stay');" class="btn-lg statuspanel__btn" :disabled="isArmed" v-bind:class="{'btn-primary':isDisarmed}" v-bind:class="{disabled:isArmed}"><h4>Stay</h4></button>
	</div>


                  </div>
                </div>

</div>
</div>

<div class="modal fade in" id="keypadmodal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">

                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                          </button>
                          <h4 class="modal-title" slot="title" id="myModalLabel">Enter your code</h4>
                        </div>
                        <div class="modal-body">



<div class="kp-container kp-container__full">
<button v-if="isFullMode == true" value="A"><i class="fa fa-ambulance"></i></button>
<button @click="setCode" value="1">1<br/><span v-if="isFullMode == true" class="subtitle">OFF</span></button>
<button @click="setCode" value="2">2<br/><span v-if="isFullMode == true" class="subtitle">AWAY</span></button>
<button @click="setCode" value="3">3<br/><span v-if="isFullMode == true" class="subtitle">STAY</span></button>
<button v-if="isFullMode == true" value="B"><i class="fa fa-fire"></i></button>
<button @click="setCode" value="4">4<br/><span v-if="isFullMode == true" class="subtitle">MAX</span></button>
<button @click="setCode" value="5">5<br/><span v-if="isFullMode == true" class="subtitle">TEST</span></button>
<button @click="setCode" value="6">6<br/><span v-if="isFullMode == true" class="subtitle subtitle-small">BYPASS</span></button>
<button v-if="isFullMode == true" value="C"><i class="fa fa-bell"></i></button>
<button @click="setCode" value="7">7<br/><span v-if="isFullMode == true" class="subtitle subtitle-small">INSTANT</span></button>
<button @click="setCode" value="8">8<br/><span v-if="isFullMode == true" class="subtitle">CODE</span></button>
<button @click="setCode" value="9">9<br/><span v-if="isFullMode == true" class="subtitle">CHIME</span></button>
<button v-if="isFullMode == true" value="D"><i class="fa fa-wheelchair"></i></button>
<button @click="setCode" value="*">*</button>
<button @click="setCode" value="0">0</button>
<button @click="setCode" value="#">#</button>
</div>



                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>

                      </div>
                    </div>
                  </div>

</div>
