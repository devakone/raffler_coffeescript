window.Raffler=
	Models:{}
	Collections:{}
	Views:{}
	Routers:{}
	init: -> 
		new Raffler.Routers.Entries
		Backbone.history.start()

class Raffler.Routers.Entries extends Backbone.Router
  routes:
  	'':'index'
  	'entries/:id':'show'
  initialize: ->
  	@collection = new Raffler.Collections.Entries()
  	@collection.fetch()
  index: ->
  	view = new Raffler.Views.EntriesIndex(collection:@collection)
  	$('#container').html(view.render().el)
  show: (id) ->
  	console.log "Entry #{id}"

class Raffler.Views.EntriesIndex extends Backbone.View
  template: _.template($('#item-template').html())
  events:
    'click #newvalue':'createEntry'
    'click #choosewinner':'drawWinner'
    'click #resetwinner':'resetWinners'
    'click li':'kill'
  initialize: ->
    @collection.on('sync', @render, this)
    @collection.on('add', @render,this)
    @collection.on('destroy', @render,this)
  render: ->
  	$(@el).html(@template(entries:@collection.toJSON()))
  	@
  createEntry: ->
    console.log $('#new-entry').val()
    @collection.create name:$('#new-entry').val()
  drawWinner: ->
    winner = @collection.shuffle()[0]
    if winner
      winner.set(winner:true)
      winner.save()
  
  resetWinners: ->
    _(@collection.models).each (item) ->
      item.set(winner:false)
      item.save()


  kill: (ev) ->
    console.log $(ev.target).attr('id') # log the jquery selector for debug
    item=@collection.find (model) ->
      model.get("id") is $(ev.target).attr('id')
    item.destroy() if item

class Raffler.Models.Entry extends Backbone.Model
  defaults: 
    name:''
    winner: false

class Raffler.Collections.Entries extends Backbone.Collection
  model: Raffler.Models.Entry
  localStorage: new Store("backbone-coffee-raffle-reset")

$(document).ready ->
  Raffler.init()
