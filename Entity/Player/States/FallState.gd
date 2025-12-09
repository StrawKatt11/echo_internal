extends PlayerState

func EnterState():
	Name = "Fall"


func ExitState():
	pass


func Draw():
	pass


func Update(delta: float):
	Player.HandleGravity(delta, Player.GravityFall)
	Player.HorizontalMovement()
	Player.HandleLanding()
	HandleAnimations()

func HandleAnimations():
	Player.animator.play("Fall")
	Player.HandleFlipH()
